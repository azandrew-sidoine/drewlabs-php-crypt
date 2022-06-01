<?php

namespace Drewlabs\Crypt\Contracts;

interface Encrypter
{
    /**
     * Encrypts $value contents
     *
     * @param string $value
     * @return bool
     */
    public function encrypt(string $value);

    /**
     * Decrypt an encrypted string value
     * 
     * @param string $encrypted 
     * @return string 
     */
    public function decrypt(string $encrypted);
}