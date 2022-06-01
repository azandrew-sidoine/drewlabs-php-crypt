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

namespace Drewlabs\Crypt\Encrypter;

use Drewlabs\Crypt\Key as CryptKey;

/**
 * Provides a Stringeable object for generating cipher used by
 * {@see Encrypter} class for encrypting and decrypting file contents.
 */
class Key
{
    /**
     * @var string
     */
    private $cipher;

    /**
     * @var string
     */
    private $value;

    /**
     * Creates an instance of Key.
     *
     * @return self
     */
    public function __construct(?string $value = null, string $cipher = 'AES-128-CBC')
    {
        $this->value = $value ?? static::createKey($cipher);
        $this->cipher = $cipher ?? 'AES-128-CBC';
    }

    /**
     * Create a new encryption string value for the given cipher.
     *
     * @throws \Exception
     *
     * @return string
     */
    public function __toString()
    {
        return $this->value;
    }

    /**
     * Generate a new encryption key that suits the cipher method.
     *
     * @throws \Exception
     *
     * @return Key
     */
    public static function make(string $cipher = 'AES-128-CBC')
    {
        return new self(static::createKey($cipher), $cipher);
    }

    /**
     * Returns the cipher used in generating the key.
     *
     * @return string
     */
    public function cipher()
    {
        return $this->cipher;
    }

    /**
     * @throws \LogicException
     *
     * @return string
     */
    private static function createKey(string $cipher)
    {
        // CryptKey generates a hex reprensation of a binary string
        // As characters are reprensent using 2bytes in hex encoding, the lenght of the returned string will
        // be equal $length * 2
        return CryptKey::new(\in_array(strtolower($cipher), ['aes-128-cbc', 'aes-128-gcm'], true) ? 8 : 16)->__toString();
    }
}
