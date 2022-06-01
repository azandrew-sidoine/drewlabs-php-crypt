<?php

namespace Drewlabs\Crypt\Exceptions;

use Exception;

class IOException extends Exception
{
    /**
     * 
     * @var string
     */
    private $path;

    public function __construct($message = null, $path = null)
    {
        $message = $message ?? ('IO error for path located at ' . ($path ?? 'unknown'));
        parent::__construct($message);
        $this->path = $path;
    }


    /**
     * 
     * @return string 
     */
    public function getpath()
    {
        return $this->path;
    }
}