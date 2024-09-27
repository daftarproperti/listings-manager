<?php

namespace App\Helpers;

use Mdanter\Ecc\EccFactory;
use Mdanter\Ecc\Serializer\Point\CompressedPointSerializer;
use Mdanter\Ecc\Serializer\Point\UncompressedPointSerializer;
use Mdanter\Ecc\Crypto\Key\PrivateKeyInterface;
use Mdanter\Ecc\Crypto\Key\PublicKeyInterface;
use Mdanter\Ecc\Crypto\Key\PrivateKey;

/**
 * ECIES encrypt/decrypt utility compatible with eciespy (https://github.com/ecies/py)
 */
class Ecies
{
    /**
     * @return array{string, string, string}
     */
    private static function aesGcmEncrypt(string $key, string $plaintext): array
    {
        $iv = random_bytes(16);
        $ciphertext = openssl_encrypt($plaintext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
        if (!$ciphertext || !is_string($tag)) {
            throw new \Error('Failed encrypting with aes-256-gcm');
        }

        return [$ciphertext, $iv, $tag];
    }

    /**
     * Follow ECIES protocol of eciespy:
     * https://github.com/ecies/py/blob/d07454b5493251a0f526237f21719e52cf770771/DETAILS.md
     * https://github.com/ecies/py/blob/d07454b5493251a0f526237f21719e52cf770771/ecies/__init__.py#L19
     */
    public static function encrypt(string $recipientPubHex, string $message): string
    {
        $serializer = new UncompressedPointSerializer();
        $generator = EccFactory::getSecgCurves()->generator256k1();

        $recipientPublicKey = $generator->getPublicKeyFrom(
            gmp_init(substr($recipientPubHex, 0, 64), 16),
            gmp_init(substr($recipientPubHex, 64, 64), 16),
        );

        $ephemeralPrivateKey = $generator->createPrivateKey();
        $ephemeralPublicKeyHex = $serializer->serialize($ephemeralPrivateKey->getPublicKey()->getPoint());
        $sharedSecretPoint = $recipientPublicKey->getPoint()->mul($ephemeralPrivateKey->getSecret());

        $sharedSecretFormatted = $ephemeralPublicKeyHex . $serializer->serialize($sharedSecretPoint);
        $symmetricKey = hash_hkdf('sha256', type(hex2bin($sharedSecretFormatted))->asString(), 32);
        list($ciphertext, $iv, $tag) = self::aesGcmEncrypt($symmetricKey, $message);

        $result = $ephemeralPublicKeyHex . bin2hex($iv) . bin2hex($tag) . bin2hex($ciphertext);
        return $result;
    }

    /**
     * Encrypts a small string (max 16 bytes).
     *
     * Uses simple ECIES scheme:
     * * Generate ephemeral key pair
     * * Get shared point from ECDH with recipient public key
     * * Derive symmetric key using HKDF from the shared point's X
     * * Use the symmetric key to AES encrypt the padded string (to make it 16 bytes = 1 AES block)
     * * Format the result to be: cipher text (16 bytes) + compressed ephemeral public key (33 bytes)
     */
    public static function encryptString(PublicKeyInterface $recipientPubKey, string $message): string|false
    {
        $compressedSerializer = new CompressedPointSerializer(EccFactory::getAdapter());
        $generator = EccFactory::getSecgCurves()->generator256k1();

        if (strlen($message) > 16) {
            return false;
        }

        // ECDH get symmetric key
        $ephemeralPrivateKey = $generator->createPrivateKey();
        $sharedSecretPoint = $recipientPubKey->getPoint()->mul($ephemeralPrivateKey->getSecret());
        $sharedSecretBytes = gmp_export($sharedSecretPoint->getX());
        $symmetricKey = hash_hkdf('sha256', $sharedSecretBytes, 32);

        $result = openssl_encrypt(
            str_pad($message, 16, "\0", STR_PAD_RIGHT),
            'aes-256-ecb',
            $symmetricKey,
            OPENSSL_RAW_DATA | OPENSSL_DONT_ZERO_PAD_KEY | OPENSSL_ZERO_PADDING,
        );

        if ($result === false) {
            return false;
        }

        return bin2hex($result) . $compressedSerializer->serialize($ephemeralPrivateKey->getPublicKey()->getPoint());
    }

    /**
     * Decrypts the 16+33 byte block into a string of max 16 bytes, reversing the encryption above.
     */
    public static function decryptToString(PrivateKeyInterface $privateKey, string $encrypted): string|false
    {
        $compressedSerializer = new CompressedPointSerializer(EccFactory::getAdapter());
        $curve = EccFactory::getSecgCurves()->curve256k1();

        $ciphertext = hex2bin(substr($encrypted, 0, 32));
        if ($ciphertext === false) {
            return false;
        }
        $compressedPubKeyHex = substr($encrypted, 32);

        // ECDH get symmetric key
        $ephemeralPublicKeyPoint = $compressedSerializer->unserialize($curve, $compressedPubKeyHex);
        $sharedSecretPoint = $ephemeralPublicKeyPoint->mul($privateKey->getSecret());
        $sharedSecretBytes = gmp_export($sharedSecretPoint->getX());
        $symmetricKey = hash_hkdf('sha256', $sharedSecretBytes, 32);

        $result = openssl_decrypt(
            $ciphertext,
            'aes-256-ecb',
            $symmetricKey,
            OPENSSL_RAW_DATA | OPENSSL_DONT_ZERO_PAD_KEY | OPENSSL_ZERO_PADDING,
        );

        if ($result === false) {
            return false;
        }

        return rtrim($result, "\0");
    }

    public static function privateKeyFromHex(string $privateHex): PrivateKeyInterface
    {
        return new PrivateKey(
            EccFactory::getAdapter(),
            EccFactory::getSecgCurves()->generator256k1(),
            gmp_init($privateHex, 16),
        );
    }

    public static function publicKeyFromPrivateHex(string $privateHex): PublicKeyInterface
    {
        return self::privateKeyFromHex($privateHex)->getPublicKey();
    }

    public static function publicHexFromPrivateHex(string $privateHex): string
    {
        $privateKey = self::privateKeyFromHex($privateHex);
        $serializer = new UncompressedPointSerializer();
        return $serializer->serialize($privateKey->getPublicKey()->getPoint());
    }
}
