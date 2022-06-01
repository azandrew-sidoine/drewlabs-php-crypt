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

namespace Drewlabs\Crypt;

class Key
{
    /**
     * @var string
     */
    private $key;

    /**
     * @return self
     */
    private function __construct()
    {
    }

    public function __toString()
    {
        return $this->key;
    }

    /**
     * Creates a new key instance.
     *
     * @param int $length
     *
     * @throws \LogicException
     *
     * @return Key
     */
    public static function new($length = 11)
    {
        $self = new self();
        $self = $self->setKey(static::createIv($length));

        return $self;
    }

    /**
     * $key property setter function.
     *
     * @return $this
     */
    private function setKey(string $key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @param int $length
     *
     * @throws Exception
     * @throws \LogicException
     *
     * @return string
     */
    private static function createIv($length = 11)
    {
        if (\function_exists('random_bytes')) {
            return bin2hex(random_bytes($length));
        } elseif (\function_exists('openssl_random_pseudo_bytes')) {
            return bin2hex(openssl_random_pseudo_bytes($length));
        } elseif (\function_exists('mcrypt_create_iv')) {
            return bin2hex(mcrypt_create_iv($length, MCRYPT_DEV_URANDOM));
        }
        throw new \LogicException('Random string function missing from your php installation');
    }
}
