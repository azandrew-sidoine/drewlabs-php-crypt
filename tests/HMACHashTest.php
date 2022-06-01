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

use Drewlabs\Crypt\HMAC\Hash as HMACHash;
use Drewlabs\Crypt\Utils;
use PHPUnit\Framework\TestCase;

class HMACHashTest extends TestCase
{
    public function test_hmac_hash_make()
    {
        $hash = HMACHash::new();
        $this->assertInstanceOf(HMACHash::class, $hash->make('Hello World!'));
        $this->assertIsString((string) $hash);
    }

    public function test_hmac_hash_check()
    {
        $hash = HMACHash::new('md5');
        $this->assertTrue($hash->make('Hello World!')->check('Hello World!'));
    }

    public function test_hmac_hash_check_on_array()
    {
        $hash = HMACHash::new()->make([
            [
                'weight' => '30pd',
                'name' => 'Banana',
            ],
            [
                'weight' => '10pd',
                'name' => 'Orange',
            ],
            [
                'name' => 'Apple',
                'weight' => '20pd',
            ],
        ])->__toString();

        $this->assertTrue(HMACHash::raw($hash)->check([
            [
                'weight' => '30pd',
                'name' => 'Banana',
            ],
            [
                'name' => 'Orange',
                'weight' => '10pd',
            ],
            [
                'name' => 'Apple',
                'weight' => '20pd',
            ],
        ]));
        $options = Utils::after('$', Utils::before('$.', $hash));
        $this->assertTrue(HMACHash::raw(Utils::after('$'.$options.'$.', $hash), $options)->check([
            [
                'weight' => '30pd',
                'name' => 'Banana',
            ],
            [
                'name' => 'Orange',
                'weight' => '10pd',
            ],
            [
                'name' => 'Apple',
                'weight' => '20pd',
            ],
        ]));

        $this->assertFalse(HMACHash::raw($hash)->check([
            [
                'name' => 'Orange',
                'weight' => '10pd',
            ],
            [
                'name' => 'Apple',
                'weight' => '20pd',
            ],
            [
                'weight' => '30pd',
                'name' => 'Banana',
            ],
        ]));

        $this->assertFalse(HMACHash::raw($hash)->check([
            [
                'weight' => '30pd',
                'name' => 'Banana',
            ],
        ]));
    }
}
