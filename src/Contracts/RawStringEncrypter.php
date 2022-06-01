<?php

namespace Drewlabs\Crypt\Contracts;

interface RawStringEncrypter extends Encrypter
{
    /**
     * Returns the content of the encryption operation
     * 
     * @return string 
     */
    public function getContents();

}