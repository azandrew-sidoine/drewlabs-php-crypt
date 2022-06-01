<?php

namespace Drewlabs\Crypt\Exceptions;

use Exception;

class MetadataException extends Exception
{
    public function __construct(string $path, string $errorMessage, string $attribute)
    {
        parent::__construct(sprintf('Cannot retrieve %s informations at "%s". %s', $attribute, $path, $errorMessage));
    }
}