<?php

use Drewlabs\Crypt\Encrypter\Crypt;
use PHPUnit\Framework\TestCase;

class EncrypterTest extends TestCase
{

    public function test_encrypter_encrypt()
    {
        $crypt = Crypt::new();
        $this->assertIsString($cipher = $crypt->encryptString('Hello World!'));
        $plainText = $crypt->decryptString($cipher);
        $this->assertEquals($plainText, 'Hello World!');
    }

}