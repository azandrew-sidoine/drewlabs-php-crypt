<?php

namespace Drewlabs\Crypt\Proxy;

use Drewlabs\Crypt\Passwords\HashFactory;

use Drewlabs\Crypt\Contracts\HashManager;
use InvalidArgumentException;

if (!function_exists('usePasswordManager')) {

    /**
     * Creates a password manager to use in creating encrypted password
     * 
     * @param string $type 
     * @return HashManager 
     * 
     * @throws InvalidArgumentException
     */
    function usePasswordManager(string $type = PASSWORD_BCRYPT) {
        return HashFactory::new()->make($type)->resolve();
    }
}