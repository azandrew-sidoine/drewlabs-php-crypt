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

namespace Drewlabs\Crypt\HMAC;

use Drewlabs\Crypt\Key;
use Drewlabs\Crypt\Utils;
use InvalidArgumentException;
use LogicException;
use RuntimeException;
use Tuupola\Base62;

final class Hash
{
    /**
     * @var string|Key
     */
    private $key;

    /**
     * @var string
     */
    private $alg;

    /**
     * @var string
     */
    private $hash;

    /**
     * Protected against class construcion using new.
     *
     * @return self
     */
    private function __construct()
    {
    }

    /**
     * Returns the string representation of the hashed input.
     *
     * @return string
     */
    public function __toString()
    {
        if (!\is_string($this->hash)) {
            throw new \LogicException('Missing hashed content');
        }
        return sprintf('%s.%s', $this->stringifyConfigs(), $this->hash);
    }

    /**
     * Creates an instance of the HMAC Hash object.
     *
     * @param string $alg
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     *
     * @return self
     */
    public static function new($alg = 'sha256', ?string $key = null)
    {
        if (!\in_array($alg, $supported_algs = hash_hmac_algos(), true)) {
            throw new \InvalidArgumentException("$alg is not in the support list of algorithms, Supported values are ".(implode(', ', $supported_algs)));
        }
        $self = new self();
        $self->alg = $alg;
        $self->key = $key ?? Key::new();

        return $self;
    }

     /**
     * Creates a class instance from a string representation of the object.
      * 
      * @param string $hash 
      * @param null|string $options 
      * @return Hash 
      * @throws InvalidArgumentException 
      * @throws LogicException 
      */
    public static function raw(string $hash, ?string $options = null)
    {
        // If the option is provided as a separate parameter, we simply use the provided option
        // else we try to read the options from hash string
        $options = $options ?? Utils::after('$', Utils::before('$.', $hash));
        if (empty($options)) {
            throw new InvalidArgumentException('hash mismatch');
        }
        [$alg, $key] = static::parseHashOptions($options);
        // We check if the provided hash starts with the options definitions
        // If not, we simply use the the entire hash as raw, else we trim the option definition from the entire hash
        $hash = Utils::strStartsWith($hash, '$'.$options.'$.') ? Utils::after('$'.$options.'$.', $hash) : $hash;
        // We use the key and algorithm to create a new instance
        $self = static::new($alg, $key);
        $self->hash($hash);

        return $self;
    }

    /**
     * Creates hash from user provided data as input.
     *
     * @param object|array|string $data
     *
     * @throws \InvalidArgumentException
     *
     * @return self
     */
    public function make($data)
    {
        $hash = hash_hmac($this->getAlg(), Utils::stringify($data), $this->getKey());
        if (false === $hash) {
            throw new \Exception('Failed hashing the data');
        }
        $this->hash = $hash;

        return $this;
    }

    /**
     * Check the plainText value against the hash value.
     *
     * **Note**
     * If the hash value is nul, we assume the HMACHash::create($hash)
     * was previously called before calling check() method
     *
     * @param object|array|string $plain
     *
     * @throws \InvalidArgumentException
     *
     * @return bool
     */
    public function check($plain, ?string $hash = null)
    {
        $self = $hash ? static::raw($hash) : $this;
        $hash = static::new($self->getAlg(), $self->getKey())->make($plain)->hash();

        return hash_equals($hash, $self->hash());
    }

    /**
     * Get/Set the hash property value.
     *
     * @return string
     */
    public function hash(?string $value = null)
    {
        if (null !== $value) {
            $this->hash = $value;
        }

        return $this->hash;
    }

    /**
     * $key property getter function.
     *
     * @return string
     */
    public function getKey()
    {
        if (!\is_string($this->key) && \is_callable($this->key)) {
            $key = \call_user_func($this->key);
        } else {
            $key = $this->key;
        }

        return null !== $key ? (string) $key : $key;
    }

    /**
     * Algorithm property getter function.
     *
     * @return string
     */
    public function getAlg()
    {
        return null !== $this->alg ? (string) $this->alg : $this->alg;
    }

    /**
     * Returns a stringify hash options that were used to compute the hash
     * 
     * @return string 
     * @throws RuntimeException 
     */
    public function hashOptions()
    {
        return $this->stringifyConfigs();
    }

    /**
     * @throws \RuntimeException
     *
     * @return string
     */
    private function stringifyConfigs()
    {
        if ((null === ($key = $this->getKey())) || (null === ($alg = $this->getAlg()))) {
            throw new \RuntimeException('Missing key or algorith properties');
        }
        // $base_62(salt=<SALT>;algo=<ALGO>)$
        return sprintf('$%s$', (new Base62())->encode(sprintf('salt=%s;alg=%s', $key, $alg)));
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @return (string|null)[]
     */
    private static function parseHashOptions(string $string)
    {
        $key = $alg = null;
        $exploded = explode(';', (new Base62())->decode($string));
        foreach ($exploded as $value) {
            $exploded2 = explode('=', $value);
            if ('salt' === $exploded2[0]) {
                $key = $exploded2[1] ?? null;
            }
            if ('alg' === $exploded2[0]) {
                $alg = $exploded2[1] ?? null;
            }
        }

        return [$alg, $key];
    }
}
