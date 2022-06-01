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

interface Encrypter
{
    /**
     * Encrypts $value contents.
     *
     * @return bool
     */
    public function encrypt(string $value);

    /**
     * Decrypt an encrypted string value.
     *
     * @return string
     */
    public function decrypt(string $encrypted);
}
