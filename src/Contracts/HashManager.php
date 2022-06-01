<?php

namespace Drewlabs\Crypt\Contracts;

interface HashManager
{
    /**
     * Hash a given string with or without options.
     *
     * @param string $value
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    public function make($value, array $options = []): ?string;

    /**
     * Check a string against a hashed value.
     *
     * @param string $plain
     * @param string $hash
     */
    public function check($plain, $hash, array $options = []): bool;
}