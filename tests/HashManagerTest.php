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

use Drewlabs\Crypt\Contracts\HashManager;
use function Drewlabs\Crypt\Proxy\usePasswordManager;

use PHPUnit\Framework\TestCase;

class HashManagerTest extends TestCase
{
    public function test_hash_factory()
    {
        $this->assertInstanceOf(HashManager::class, usePasswordManager('bcrypt'));
        $this->assertInstanceOf(HashManager::class, usePasswordManager(\PASSWORD_ARGON2I));
    }

    public function test_bcrypt_manager()
    {
        $manager = usePasswordManager('bcrypt');
        $password = $manager->make('Secret8');
        $this->assertIsString($password);
        $this->assertTrue($manager->check('Secret8', $password));
    }

    public function test_argon2_manager()
    {
        $manager = usePasswordManager(\PASSWORD_ARGON2I);
        $password = $manager->make('Secret8');
        $this->assertIsString($password);
        $this->assertTrue($manager->check('Secret8', $password));
    }

    public function test_md5_manager()
    {
        $manager = usePasswordManager('md5');
        $password = $manager->make('Secret8');
        $this->assertIsString($password);
        $this->assertTrue($manager->check('Secret8', $password));
    }
}
