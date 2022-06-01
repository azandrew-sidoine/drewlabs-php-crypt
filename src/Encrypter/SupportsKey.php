<?php

namespace Drewlabs\Crypt\Encrypter;

use Drewlabs\Crypt\Key as CryptKey;
use Drewlabs\Crypt\Utils;
use Exception;
use LogicException;

/**
 * @internal
 */
trait SupportsKey
{

    /**
     * The encryption key.
     *
     * @var string
     */
    private $key;

    /**
     * The algorithm used for encryption.
     *
     * @var string
     */
    private $cipher;


    private function setEncryptionProperties(Key $key)
    {
        // If the key starts with "base64:", we will need to decode the key before handing
        // it off to the encrypter. Keys may be base-64 encoded for presentation and we
        // want to make sure to convert them back to the raw bytes before encrypting.
        if (Utils::strStartsWith($key_ = (string)$key, 'base64:')) {
            $key_ = base64_decode(substr($key_, strlen('base64:')));
        }
        if ($this->supported($key_, $cipher = $key->cipher())) {
            $this->key = $key_;
            $this->cipher = $cipher;
        } else {
            throw new Exception('The only supported ciphers are aes-128-cbc and aes-128-cbc, aes-128-gcm, aes-256-gcm with the correct key lengths.');
        }
    }

    /**
     * 
     * @return string 
     * @throws LogicException 
     */
    private function createIV()
    {
        // Key length is divided by 2 because the CryptKey::new()->__toString() returns a hex
        // representation of a binary string which will encode each character on 2 bytes
        // causing the returned key to double in size
        return CryptKey::new(openssl_cipher_iv_length(strtolower($this->cipher)) / 2)->__toString();
    }

    /**
     * Determine if the given key and cipher combination is valid.
     *
     * @param  string  $key
     * @param  string  $cipher
     * @return bool
     */
    private function supported($key, $cipher)
    {
        if (!in_array($cipher = strtolower($cipher), array_keys($this->getSupportedCiphers()))) {
            return false;
        }
        return mb_strlen($key, '8bit') === $this->getSupportedCiphers()[$cipher]['size'] ?? null;
    }


    /**
     * 
     * @return ((int|false)[]|(int|true)[])[] 
     */
    private function getSupportedCiphers()
    {
        return [
            'aes-128-cbc' => ['size' => 16, 'aead' => false],
            'aes-256-cbc' => ['size' => 32, 'aead' => false],
            'aes-128-gcm' => ['size' => 16, 'aead' => true],
            'aes-256-gcm' => ['size' => 32, 'aead' => true],
        ];
    }
}
