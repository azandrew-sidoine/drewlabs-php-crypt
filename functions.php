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

namespace Drewlabs\Crypt\Proxy;

use Drewlabs\Crypt\Contracts\HashManager;

use Drewlabs\Crypt\Passwords\Factory;

if (!\function_exists('usePasswordManager')) {

    /**
     * Creates a password manager to use in creating encrypted password.
     *
     * @throws \InvalidArgumentException
     *
     * @return HashManager
     */
    function usePasswordManager(string $type = \PASSWORD_BCRYPT)
    {
        return Factory::new()->make($type)->resolve();
    }
}
