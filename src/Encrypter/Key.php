<?php

namespace Drewlabs\Crypt\Encrypter;

use Drewlabs\Crypt\Key as CryptKey;
use Exception;
use LogicException;

/**
 * Provides a Stringeable object for generating cipher used by
 * {@see Encrypter} class for encrypting and decrypting file contents
 * 
 * @package Drewlabs\Crypt\Encrypter
 */
class Key
{
    /**
     * 
     * @var string
     */
    private $cipher;

    /**
     * 
     * @var string
     */
    private $value;

    /**
     * Creates an instance of Key
     * 
     * @param string|null $value 
     * @param string $cipher 
     * @return self 
     */
    public function __construct(string $value = null, string $cipher = 'AES-128-CBC')
    {
        $this->value = $value ?? static::createKey($cipher);
        $this->cipher = $cipher ?? 'AES-128-CBC';
    }

    /**
     * Generate a new encryption key that suits the cipher method
     * 
     * @param string $cipher 
     * @return Key 
     * @throws Exception 
     */
    public static function make(string $cipher = 'AES-128-CBC')
    {
        return new self(static::createKey($cipher), $cipher);
    }

    /**
     * 
     * @param string $cipher 
     * @return string 
     * @throws LogicException 
     */
    private static function createKey(string $cipher)
    {
        // CryptKey generates a hex reprensation of a binary string
        // As characters are reprensent using 2bytes in hex encoding, the lenght of the returned string will
        // be equal $length * 2
        return CryptKey::new(in_array(strtolower($cipher), ['aes-128-cbc', 'aes-128-gcm']) ? 8 : 16)->__toString();
    }

    /**
     * Returns the cipher used in generating the key
     * 
     * @return string 
     */
    public function cipher()
    {
        return $this->cipher;
    }

    /**
     * Create a new encryption string value for the given cipher.
     * 
     * @return string
     * 
     * @throws Exception 
     */
    public function __toString()
    {
        return $this->value;
    }
}