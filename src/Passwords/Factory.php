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

namespace Drewlabs\Crypt\Passwords;

use Drewlabs\Crypt\Contracts\HashManager;

class Factory
{
    /**
     * Hash instance.
     *
     * @var HashManager
     */
    private $instance;

    /**
     * @return self
     */
    private function __construct()
    {
    }

    /**
     * @return void
     */
    public function __destruct()
    {
        unset($this->instance);
    }

    /**
     * Creates an instance of the factory object.
     *
     * @return Factory
     */
    public static function new()
    {
        return new self();
    }

    /**
     * Make a new Factory class.
     *
     * @param string $type bcrypt|argon2|md5
     */
    public function make($type = 'bcrypt')
    {
        $type = $type ?: 'bcrypt';
        switch (strtolower($type)) {
            case 'bcrypt':
            case \PASSWORD_BCRYPT:
                $this->instance = new BCrypt();
                break;
            case 'argon2':
            case \PASSWORD_ARGON2I:
                $this->instance = new Argon2();
                break;
            case 'md5':
                $this->instance = new MD5();
                break;
            default:
                throw new \InvalidArgumentException('Unimplemented hashing algorithm');
        }

        return $this;
    }

    /**
     * Resolve the constructed instance.
     *
     * @return HashManager
     */
    public function resolve()
    {
        return $this->instance;
    }
}
