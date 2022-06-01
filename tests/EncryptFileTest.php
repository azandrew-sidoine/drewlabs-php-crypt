<?php

use Drewlabs\Crypt\Encrypter\Crypt;
use PHPUnit\Framework\TestCase;

class EncryptFileTest extends TestCase
{
    public function test_encrypt_function()
    {
        $crypt = Crypt::new("base64:" . base64_encode('SuperRealSecretA'));
        $result = $crypt->encryptBlob(__DIR__ . '/storage/text.txt', __DIR__ . '/storage/text.txt.enc'); //
        $crypt->encryptBlob(__DIR__ . '/storage/ng-connect.png', __DIR__ . '/storage/ng-connect.png.enc');
        $this->assertTrue($result);
        $this->assertTrue(is_file(__DIR__ . '/storage/text.txt.enc'));
    }

    public function test_decrypt_function()
    {
        $crypt = Crypt::new("base64:" . base64_encode('SuperRealSecretA'));
        $result = $crypt->decryptBlob(__DIR__ . '/storage/text.txt.enc', __DIR__ . '/storage/decrypted/text.txt');
        $crypt->decryptBlob(__DIR__ . '/storage/ng-connect.png.enc', __DIR__ . '/storage/decrypted/ng-connect.png');
        $this->assertTrue($result);
        $this->assertTrue(file_get_contents(__DIR__ . '/storage/text.txt') === file_get_contents(__DIR__ . '/storage/text.txt'));
    }
}
