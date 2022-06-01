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

namespace Drewlabs\Crypt\Contracts;

interface HashManager
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
    public function make($value, array $options = []): ?string;

    /**
     * Check a string against a hashed value.
     *
     * @param string $plain
     * @param string $hash
     */
    public function check($plain, $hash, array $options = []): bool;
}
