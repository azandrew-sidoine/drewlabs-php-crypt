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

namespace Drewlabs\Crypt\Encrypter;

use Drewlabs\Crypt\Encrypter\Encrypter as CoreEncrypter;
use Drewlabs\Crypt\Exceptions\DecryptionException;
use Drewlabs\Crypt\Exceptions\EncryptionException;
use Drewlabs\Crypt\Exceptions\MetadataException;

class Crypt
{
    /**
     * @var Key
     */
    private $key;

    private function __construct()
    {
    }

    /**
     * @return self
     */
    public static function new(?string $key = null, string $cipher = 'AES-128-CBC')
    {
        $self = new self();
        $self->key = new Key($key, $cipher);

        return $self;
    }

    /**
     * Encrypt a given string and return the encrypted string.
     *
     * @throws EncryptionException
     *
     * @return string
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
     * Decrypt an encrypted string and return the raw string.
     *
     * @throws DecryptionException
     *
     * @return string
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
     * Creates an encrypted blob from a source path.
     *
     * @param string|resource|string $dstPath
     *
     * @throws EncryptionException
     * @throws LogicException
     * @throws MetadataException
     *
     * @return bool
     */
    public function encryptBlob(string $path, $dstPath)
    {
        $encrypter = FileEncrypter::new($this->key, $dstPath);

        return $encrypter->encrypt($path);
    }

    /**
     * Decrypt an encrypted content into a destrination resource.
     *
     * @param string|resource|string $dstPath
     *
     * @throws EncryptionException
     * @throws LogicException
     * @throws MetadataException
     *
     * @return bool
     */
    public function decryptBlob(string $path, $dstPath)
    {
        $encrypter = FileEncrypter::new($this->key, $dstPath);

        return $encrypter->decrypt($path);
    }
}
