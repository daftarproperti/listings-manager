<?php

namespace App\Helpers;

use Mdanter\Ecc\EccFactory;
use Mdanter\Ecc\Serializer\Point\UncompressedPointSerializer;
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

    public static function publicHexFromPrivateHex(string $privateHex): string
    {
        $privateKey = new PrivateKey(
            EccFactory::getAdapter(),
            EccFactory::getSecgCurves()->generator256k1(),
            gmp_init($privateHex, 16),
        );

        $serializer = new UncompressedPointSerializer();
        return $serializer->serialize($privateKey->getPublicKey()->getPoint());
    }
}
