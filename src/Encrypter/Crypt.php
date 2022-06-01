<?php

namespace Drewlabs\Crypt\Encrypter;

use Drewlabs\Crypt\Encrypter\Encrypter as CoreEncrypter;
use Drewlabs\Crypt\Encrypter\FileEncrypter;
use Drewlabs\Crypt\Encrypter\Key;
use Drewlabs\Crypt\Exceptions\DecryptionException;
use Drewlabs\Crypt\Exceptions\EncryptionException;
use Drewlabs\Crypt\Exceptions\MetadataException;

class Crypt
{
    /**
     * 
     * @var Key
     */
    private $key;


    private function __construct()
    {
    }

    /**
     * 
     * @param string $cipher 
     * @param string|null $key 
     * @return self 
     */
    public static function new(string $key = null, string $cipher = 'AES-128-CBC')
    {
        $self = new self;
        $self->key = new Key($key, $cipher);
        return $self;
    }

    /**
     * Encrypt a given string and return the encrypted string
     * 
     * @param string $value 
     * @return string 
     * @throws EncryptionException 
     */
    public function encryptString(string $value)
    {
        $encrypter = CoreEncrypter::new($this->key);
        $result = $encrypter->encrypt($value);
        if ($result) {
            return $encrypter->getContents();
        }
        throw new EncryptionException('Encryption failed with unknown reason');
    }

    /**
     * Decrypt an encrypted string and return the raw string
     * 
     * @param string $encrypted 
     * @return string 
     * @throws DecryptionException 
     */
    public function decryptString(string $encrypted)
    {
        $encrypter = CoreEncrypter::new($this->key);
        $result = $encrypter->decrypt($encrypted);
        if ($result) {
            return $encrypter->getContents();
        }
        throw new DecryptionException('Decrypt failed with unknown reason');
    }

    /**
     * Creates an encrypted blob from a source path
     * 
     * @param string $path 
     * @param string|resource|string $dstPath 
     * @return bool 
     * @throws EncryptionException 
     * @throws LogicException 
     * @throws MetadataException 
     */
    public function encryptBlob(string $path, $dstPath)
    {
        $encrypter = FileEncrypter::new($this->key, $dstPath);
        return $encrypter->encrypt($path);
    }

    /**
     * Decrypt an encrypted content into a destrination resource
     * 
     * @param string $path 
     * @param string|resource|string $dstPath 
     * @return bool 
     * @throws EncryptionException 
     * @throws LogicException 
     * @throws MetadataException 
     */
    public function decryptBlob(string $path, $dstPath)
    {
        $encrypter = FileEncrypter::new($this->key, $dstPath);
        return $encrypter->decrypt($path);
    }
}
