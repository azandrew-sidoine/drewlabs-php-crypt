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

use Drewlabs\Crypt\Contracts\Encrypter as ContractsEncrypter;
use Drewlabs\Crypt\Encrypter\Key as EncrypterKey;
use Drewlabs\Crypt\Exceptions\DecryptionException;
use Drewlabs\Crypt\Exceptions\EncryptionException;
use Drewlabs\Crypt\Utils;

final class Encrypter implements ContractsEncrypter
{
    use SupportsKey;

    /**
     * @var string
     */
    private $encryptedText;

    /**
     * @var string
     */
    private $plainText;

    /**
     * @var bool
     */
    private $encrypting = true;

    /**
     * Private constructor that prevent creating object using new.
     *
     * @return self
     */
    private function __construct()
    {
    }

    /**
     * Create a new encrypter instance.
     *
     * @throws \Exception
     *
     * @return self
     */
    public static function new(EncrypterKey $key)
    {
        $self = new self();
        $self->setEncryptionProperties($key);

        return $self;
    }

    /**
     * Set path to which the encrypted or decripted content is written.
     *
     * @param mixed $path
     *
     * @return void
     */
    public function writeTo(string $path)
    {
        $this->writePath = $path;
    }

    public function encrypt($value)
    {
        $this->encrypting = true;
        $vector = $this->createIV();
        $value = @openssl_encrypt(Utils::stringify($value), strtolower($this->cipher), $this->key, 0, $vector, $tag);
        if (false === $value) {
            throw new EncryptionException('Could not encrypt the data.');
        }
        $string = Utils::stringify($this->makePayload($value, $vector, $tag), \JSON_UNESCAPED_SLASHES);
        if (\JSON_ERROR_NONE !== json_last_error()) {
            throw new EncryptionException('Could not encrypt the data.');
        }
        $this->encryptedText = Utils::base62Encode($string);

        return true;
    }

    public function decrypt($encrypted)
    {
        $this->encrypting = false;
        $payload = $this->parsePayload($encrypted);
        $iv = $payload['iv'];
        $this->validateTag($tag = empty($payload['tag']) ? null : $payload['tag']);
        $decrypted = openssl_decrypt($payload['value'], strtolower($this->cipher), $this->key, 0, $iv, $tag ?? '');
        if (false === $decrypted) {
            throw new DecryptionException('Could not decrypt the data.');
        }
        $this->plainText = Utils::parse($decrypted);

        return true;
    }

    public function getContents()
    {
        return $this->encrypting ? $this->encryptedText : $this->plainText;
    }

    /**
     * Reset the state of the current object.
     *
     * @return void
     */
    public function resetState()
    {
        $this->encryptedText = null;
        $this->plainText = null;
        $this->encrypting = true;
    }

    /**
     * Create a MAC for the given value.
     *
     * @param string $vector
     * @param mixed  $value
     *
     * @return string
     */
    private function hash($vector, $value)
    {
        return hash_hmac('sha256', $vector.$value, $this->key);
    }

    /**
     * Get the JSON array from the given payload.
     *
     * @param string $payload
     *
     * @throws DecryptionException
     *
     * @return array
     */
    private function parsePayload($payload)
    {
        /**
         * @var array
         */
        $payload = Utils::parse(Utils::base62Decode($payload, true), true);
        if (!$this->validPayload($payload)) {
            throw new DecryptionException('The payload is invalid.');
        }
        if (!$this->getSupportedCiphers()[strtolower($this->cipher)]['aead'] && !$this->validMac($payload)) {
            throw new DecryptionException('The MAC is invalid.');
        }

        return $payload;
    }

    /**
     * @param string $value
     * @param string $vector
     * @param mixed  $tag
     *
     * @return array
     */
    private function makePayload($value, $vector, $tag = null)
    {
        $mac = (bool) ($this->getSupportedCiphers()[strtolower($this->cipher)]['aead']) ? '' : $this->hash($vector, $value);

        return ['iv' => $vector, 'value' => $value, 'mac' => $mac, 'tag' => $tag];
    }

    /**
     * Verify that the encryption payload is valid.
     *
     * @param mixed $payload
     *
     * @return bool
     */
    private function validPayload($payload)
    {
        return \is_array($payload) && isset($payload['iv'], $payload['value'], $payload['mac']) &&
            \strlen($payload['iv']) === openssl_cipher_iv_length(strtolower($this->cipher));
    }

    /**
     * @return bool
     */
    private function validMac(array $payload)
    {
        return hash_equals($this->hash($payload['iv'], $payload['value']), $payload['mac']);
    }

    /**
     * @param mixed $tag
     *
     * @throws DecryptionException
     *
     * @return void
     */
    private function validateTag($tag)
    {
        if ($this->getSupportedCiphers()[strtolower($this->cipher)]['aead'] && 16 !== \strlen($tag)) {
            throw new DecryptionException('Could not decrypt the data.');
        }
        if (!$this->getSupportedCiphers()[strtolower($this->cipher)]['aead'] && \is_string($tag)) {
            throw new DecryptionException('Unable to use tag because the cipher algorithm does not support AEAD.');
        }
    }
}
