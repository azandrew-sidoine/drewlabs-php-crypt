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

class HashBase
{
    /**
     * @var int
     */
    protected $rounds = 10;

    /**
     * Makes a hashed value based on a string.
     *
     * @param string $value
     * @param string $algo
     * @param array
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    protected function hash($value, $algo, $options = [])
    {
        $hashed_value = password_hash($value, $algo, $options);
        if ($hashed_value) {
            return $hashed_value;
        }
        throw new \RuntimeException("$algo hashing algorithm is not supported");
    }

    /**
     * Check hashed value against a given string.
     *
     * @param string $value
     * @param string $hashed_value
     * @param array  $options
     */
    protected function hashCompare($value, $hashed_value, $options = []): bool
    {
        return (isset($hashed_value) || (!empty($hashed_value))) ? password_verify($value, $hashed_value) : false;
    }

    /**
     * Verify if hashed_value has been compute using a given options.
     *
     * @param string $hashed_value
     * @param string $algo
     */
    protected function passwordNeedsRehash($hashed_value, $algo, array $options = []): bool
    {
        return password_needs_rehash($hashed_value, $algo, $options);
    }
}
