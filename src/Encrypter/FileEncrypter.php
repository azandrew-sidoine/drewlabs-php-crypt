<?php

namespace Drewlabs\Crypt\Encrypter;

use Drewlabs\Crypt\Contracts\Encrypter;
use Drewlabs\Crypt\Encrypter\Key as EncrypterKey;
use Drewlabs\Crypt\Exceptions\DecryptionException;
use Drewlabs\Crypt\Exceptions\EncryptionException;
use Drewlabs\Crypt\Exceptions\FileNotFoundException;
use Drewlabs\Crypt\Exceptions\IOException;
use Drewlabs\Crypt\Exceptions\MetadataException;
use Drewlabs\Crypt\Utils;

/**
 * 
 * @package Drewlabs\Crypt\Encrypter
 */
final class FileEncrypter implements Encrypter
{
    use SupportsKey;

    /**
     * Define the number of blocks that should be read from the source file for each chunk.
     * We chose 255 because on decryption we want to read chunks of 4kb ((255 + 1)*16).
     */
    private const FILE_ENCRYPTION_BLOCKS = 255;


    /**
     * Source file handle
     * 
     * @var resource|false
     */
    private $handle;

    /**
     * Destination file handle
     * 
     * @var resource|false
     */
    private $dstHandle;

    /**
     * 
     * @var string
     */
    private $writePath;


    /**
     * Private constructor that prevent creating object using new
     * 
     * @return self 
     */
    private function __construct()
    {
    }

    /**
     * Create a new encrypter instance.
     * 
     * @param EncrypterKey $key 
     * @param string|resource|string $writePath 
     * @return self 
     * @throws Exception 
     */
    public static function new(EncrypterKey $key, $writePath)
    {
        $self = new self;
        $self->setEncryptionProperties($key);
        $self->writePath = $writePath;
        return $self;
    }

    /**
     * Set path to which the encrypted or decripted content is written 
     * 
     * @param mixed $path 
     * @return void 
     */
    public function writeTo(string $path)
    {
        $this->writePath = $path;
    }

    public function encrypt($value)
    {
        // We keep reference to file descriptors in private properties
        // so that in case of unhandled exceptions, we successfully cleanup
        // resources using class destructors
        $this->handle = $this->openSourceFile($value);
        $this->dstHandle = $this->openDestFile();
        if (!is_resource($this->handle) || !is_resource($this->dstHandle)) {
            throw new EncryptionException('Encryption Error: Fail to open source or destination files');
        }
        // Put the initialzation vector to the beginning of the destination file
        $vector = $this->createIV();
        fwrite($this->dstHandle, $vector);
        $chunkSize = ceil($this->getFileSize($value) / (16 * self::FILE_ENCRYPTION_BLOCKS));
        $index = 0;
        while (!feof($this->handle)) {
            $plain = @fread($this->handle, 16 * self::FILE_ENCRYPTION_BLOCKS);
            if (false === $plain) {
                throw new EncryptionException("Encryption Error: Failed to read from source file at $value");
            }
            $cipher = @openssl_encrypt($plain, $this->cipher, $this->key, OPENSSL_RAW_DATA, $vector);
            // Because Amazon S3 will randomly return smaller sized chunks:
            // Check if the size read from the stream is different than the requested chunk size
            // In this scenario, request the chunk again, unless this is the last chunk
            if (
                strlen($plain) !== 16 * self::FILE_ENCRYPTION_BLOCKS
                && $index + 1 < $chunkSize
            ) {
                fseek($this->handle, 16 * self::FILE_ENCRYPTION_BLOCKS * $index);
                continue;
            }
            // Use the first 16 bytes of the ciphertext as the next initialization vector
            $vector = substr($cipher, 0, 16);
            fwrite($this->dstHandle, $cipher);
            $index++;
        }
        $this->closeHandles();
        return true;
    }

    public function decrypt($encrypted)
    {
        // We keep reference to file descriptors in private properties
        // so that in case of unhandled exceptions, we successfully cleanup
        // resources using class destructors
        $this->handle = $this->openSourceFile($encrypted);
        $this->dstHandle = $this->openDestFile();
        if (!is_resource($this->handle) || !is_resource($this->dstHandle)) {
            throw new DecryptionException('Decryption Error: Fail to open source or destination files');
        }

        // Get the initialzation vector from the beginning of the file
        $vector = fread($this->handle, 16);
        $chinkSize = ceil((filesize($encrypted) - 16) / (16 * (self::FILE_ENCRYPTION_BLOCKS + 1)));
        $index = 0;
        while (!feof($this->handle)) {
            // We have to read one block more for decrypting than for encrypting because of the initialization vector
            $cipher = fread($this->handle, 16 * (self::FILE_ENCRYPTION_BLOCKS + 1));
            if (false === $cipher) {
                throw new DecryptionException("Decryption Error: Failed to read from source file at $encrypted");
            }
            $plain = openssl_decrypt($cipher, $this->cipher, $this->key, OPENSSL_RAW_DATA, $vector);
            // Because Amazon S3 will randomly return smaller sized chunks:
            // Check if the size read from the stream is different than the requested chunk size
            // In this scenario, request the chunk again, unless this is the last chunk
            if (
                strlen($cipher) !== 16 * (self::FILE_ENCRYPTION_BLOCKS + 1)
                && $index + 1 < $chinkSize
            ) {
                fseek($this->handle, 16 + 16 * (self::FILE_ENCRYPTION_BLOCKS + 1) * $index);
                continue;
            }

            if ($plain === false) {
                throw new DecryptionException('Decryption failed');
            }
            // Get the the first 16 bytes of the ciphertext as the next initialization vector
            $vector = substr($cipher, 0, 16);
            fwrite($this->dstHandle, $plain);
            $index++;
        }
        $this->closeHandles();
        return true;
    }

    /**
     * 
     * @return resource|false 
     * @throws Exception 
     */
    private function openDestFile()
    {
        if (is_resource($this->writePath)) {
            return $this->writePath;
        }
        if (($fd = fopen($this->writePath, 'w')) === false) {
            throw new IOException('Cannot open file for writing', $this->writePath);
        }
        return $fd;
    }

    /**
     * 
     * @param mixed $path 
     * @return resource|false 
     * @throws Exception 
     */
    private function openSourceFile($path)
    {
        $opts = Utils::strStartsWith($path, 's3://') ? ['s3' => ['seekable' => true]] : [];
        if (($fd = @fopen($path, 'r', false, stream_context_create($opts))) === false) {
            throw new IOException("Can not open file located at path $path", $path);
        }
        return $fd;
    }

    /**
     * Extracts the file size information from the file path.
     * 
     * @param string $path 
     * @return int|false 
     * @throws FileNotFoundException 
     * @throws MetadataException 
     */
    private function getFileSize(string $path)
    {
        if (is_dir($path)) {
            throw new IOException(sprintf('file path not found at at "%s"', $path), $path);
        }
        if (($result = @filesize($path)) === false) {
            throw new MetadataException($path, error_get_last()['message'] ?? '', 'filesize');
        }
        return $result;
    }

    /**
     * 
     * @return void 
     */
    private function closeHandles()
    {
        foreach ([$this->dstHandle, $this->handle] as $value) {
            if (is_resource($value)) {
                fclose($value);
            }
        }
    }

    public function __destruct()
    {
        $this->closeHandles();
    }
}
