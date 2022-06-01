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
     * Protected against class construcion using new
     *
     * @return self
     */
    private function __construct()
    {
    }

    /**
     * Creates an instance of the HMAC Hash object
     * 
     * @param string $alg 
     * @param null|string $key 
     * @return self 
     * @throws InvalidArgumentException 
     * @throws LogicException 
     */
    public static function new($alg = 'sha256', ?string $key = null)
    {
        if (!\in_array($alg, $supported_algs = hash_hmac_algos(), true)) {
            throw new \InvalidArgumentException("$alg is not in the support list of algorithms, Supported values are " . (implode(', ', $supported_algs)));
        }
        $self = new self;
        $self->alg = $alg;
        $self->key = $key ?? Key::new();
        return $self;
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
        var_dump(sprintf('%s.%s', $this->stringifyConfigs(), $this->hash));

        return sprintf('%s.%s', $this->stringifyConfigs(), $this->hash);
    }

    /**
     * Creates a class instance from a string representation of the object.
     *
     * @return self
     */
    public static function raw(string $hash)
    {
        $config = Utils::after('$', Utils::before('$.', $hash));
        [$alg, $key] = static::getOptions($config);
        $hash = Utils::after('$' . $config . '$.', $hash);
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
    private static function getOptions(string $string)
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
