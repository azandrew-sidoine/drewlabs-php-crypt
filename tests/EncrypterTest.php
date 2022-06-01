<?php

declare(strict_types=1);

/*
 * This file is part of the Drewlabs package.
 *
 * (c) Sidoine Azandrew <azandrewdevelopper@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Drewlabs\Crypt\Encrypter\Crypt;
use PHPUnit\Framework\TestCase;

class EncrypterTest extends TestCase
{
    public function test_encrypter_encrypt()
    {
        $crypt = Crypt::new();
        $this->assertIsString($cipher = $crypt->encryptString('Hello World!'));
        $plainText = $crypt->decryptString($cipher);
        $this->assertSame($plainText, 'Hello World!');
    }
}
