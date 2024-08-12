<?php

namespace Tests\Feature;

use App\Helpers\Ecies;
use Mdanter\Ecc\EccFactory;

use Tests\TestCase;

class EciesTest extends TestCase
{
    /**
     * Test encrypting and decrypting a small string.
     */
    public function test_encrypt_string(): void
    {
        $generator = EccFactory::getSecgCurves()->generator256k1();
        $privateKey = $generator->getPrivateKeyFrom(
            gmp_init("0000000000000000000000000000000000000000000000000000000000000001"));
        $publicKey = $privateKey->getPublicKey();

        // typical case
        $encrypted = Ecies::encryptString($publicKey, "the message");
        $this->assertEquals("the message", Ecies::decryptToString($privateKey, $encrypted));

        // empty string
        $encrypted = Ecies::encryptString($publicKey, "");
        $this->assertEquals("", Ecies::decryptToString($privateKey, $encrypted));

        // exactly 16 bytes (max size, 1 block AES)
        $encrypted = Ecies::encryptString($publicKey, "abcdefghijklmnop");
        $this->assertEquals("abcdefghijklmnop", Ecies::decryptToString($privateKey, $encrypted));

        // long string
        $encrypted = Ecies::encryptString($publicKey, "abcdefghijklmno");
        $this->assertEquals("abcdefghijklmno", Ecies::decryptToString($privateKey, $encrypted));

        // longer than max size
        $encrypted = Ecies::encryptString($publicKey, "abcdefghijklmnopq");
        $this->assertFalse($encrypted);
    }
}
