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

class BCrypt extends HashBase implements HashManager
{
    /**
     * Hash a given string with or without options.
     *
     * @param string $value
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    public function make($value, array $options = []): ?string
    {
        $options['cost'] = $this->cost($options);

        return $this->hash($value, \PASSWORD_BCRYPT, $options);
    }

    /**
     * Check a string against a hashed value.
     *
     * @param string $value
     * @param string $hashed_value
     */
    public function check($value, $hashed_value, array $options = []): bool
    {
        return $this->hashCompare($value, $hashed_value, $options);
    }

    /**
     * Check if password has been hashed with given options.
     *
     * @param string $hashed_value
     * @param array  $options
     */
    public function needsRehash($hashed_value, $options): bool
    {
        $options['cost'] = $this->cost($options);

        return $this->passwordNeedsRehash($hashed_value, \PASSWORD_BCRYPT, $options);
    }

    public function setRounds($rounds)
    {
        $this->rounds = (int) $rounds;

        return $this;
    }

    /**
     * Extract the cost value from the options array.
     *
     * @return int
     */
    protected function cost(array $options = [])
    {
        return $options['rounds'] ?? $this->rounds;
    }
}
