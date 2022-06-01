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

namespace Drewlabs\Crypt;

use InvalidArgumentException;
use Tuupola\Base62;

class Utils
{
    /**
     * Returns the string after the first occurence of the provided character.
     *
     * @return string
     */
    public static function after(string $character, string $haystack)
    {
        if (!\is_bool(strpos($haystack, $character))) {
            return substr($haystack, strpos($haystack, $character) + \strlen($character));
        }

        return '';
    }

    /**
     * Returns the string before the first occurence of the provided character.
     *
     * @return string
     */
    public static function before(string $character, string $haystack)
    {
        $pos = strpos($haystack, $character);
        if ($pos) {
            return substr($haystack, 0, $pos);
        }

        return '';
    }

    /**
     * Recursively sort array by key.
     *
     * @return array
     */
    public static function recursiveksort(array $value)
    {
        return static::_recursiveksort_($value, 'ksort');
    }

    /**
     * Prepends $string to the $haystack string.
     *
     * @return string
     */
    public function prependString(string $string, string $haystack)
    {
        return sprintf('%s%s', $string, $haystack);
    }

    /**
     * Compute string representation of object|array|string variables.
     *
     * @param string|object|array $value
     * 
     * @param mixed $value 
     * @param int $flags 
     * @return string 
     * @throws InvalidArgumentException 
     */
    public static function stringify($value, int $flags = 0)
    {
        $is_object = \is_object($value);
        $is_array = \is_array($value);
        $is_string = \is_string($value);
        if (!($is_object || $is_string || $is_array)) {
            throw new \InvalidArgumentException('Expected string, array or object types, got ' . (null !== $value && \is_object($value) ? \get_class($value) : \gettype($value)));
        }
        if ($is_string) {
            return sprintf("str:%s", $value);
        }

        if ($is_object && method_exists($value, 'toArray')) {
            /**
             * @var array
             */
            $arr = $value->toArray();
        } elseif ($is_object && !method_exists($value, 'toArray')) {
            $arr = get_object_vars($value);
        } else {
            // Here we assume $value is an array as it does not under
            // previous conditions
            /**
             * @var array
             */
            $arr = array_merge($value);
        }

        return sprintf("json:%s", json_encode(static::recursiveksort($arr), $flags));
    }

    /**
     * Parse user string
     * 
     * @param string $string 
     * @param array|bool $options 
     * @return mixed 
     */
    public static function parse(string $string, $options = [])
    {
        if (static::strStartsWith($string, 'str:')) {
            return static::after('str:', $string);
        }

        if (static::strStartsWith($string, 'json:')) {
            $encoded = static::after('json:', $string);
            $options =  is_bool($options) ? [
                'array' => true,
                'depth' => 512,
                'flags' => 0
            ] : $options ?? [];
            return json_decode($encoded, $options['array'] ?? true, $options['depth'] ?? 512, $options['flags'] ?? 0);
        }

        if (static::strStartsWith($string, 'serialize:')) {
            $encoded = static::after('serialize:', $string);
            return unserialize($encoded, is_bool($options) ? [] : $options ?? []);
        }
        return json_decode($string);
    }

    /**
     * @param callable|\Closure $sortFunc
     *
     * @return array
     */
    private static function _recursiveksort_(array $value, $sortFunc)
    {
        if (null === $sortFunc) {
            $sortFunc = 'ksort';
        }
        // region Internal sort function
        $func = static function (array &$list) use ($sortFunc, &$func) {
            foreach ($list as $key => $value) {
                $is_object = \is_object($value);
                if ($is_object || \is_array($value)) {
                    $current = $is_object ? get_object_vars($value) : $value;
                    $func($current);
                    $list[$key] = $current;
                }
            }
            \call_user_func_array($sortFunc, [&$list]);
        };
        $func($value);
        // endregion Internal function
        return $value;
    }

    /**
     * Returns true if $haystack starts with $needle substring
     * 
     * @param string $haystack 
     * @param string $needle 
     * @return bool 
     */
    public static function strStartsWith(string $haystack, string $needle)
    {
        if (function_exists('str_starts_with')) {
            return str_starts_with($haystack, $needle);
        }
        return 0 === strncmp($haystack, $needle, \strlen($needle));
    }


    /**
     * 
     * @param mixed $data 
     * @return string 
     * @throws InvalidArgumentException 
     */
    public static function base62Encode($data)
    {
        if (null === $data) {
            throw new InvalidArgumentException('Data to encode must not equal null');
        }
        return (new Base62())->encode($data);
    }

    /**
     * 
     * @param string $encoded 
     * @return string 
     * @throws InvalidArgumentException 
     */
    public static function base62Decode(string $encoded)
    {
        if (null === $encoded) {
            throw new InvalidArgumentException('Data to encode must not equal null');
        }
        return (new Base62())->decode($encoded);
    }
}
