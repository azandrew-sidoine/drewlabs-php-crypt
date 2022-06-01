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

namespace Drewlabs\Crypt\Exceptions;

class IOException extends \Exception
{
    /**
     * @var string
     */
    private $path;

    public function __construct($message = null, $path = null)
    {
        $message = $message ?? ('IO error for path located at '.($path ?? 'unknown'));
        parent::__construct($message);
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getpath()
    {
        return $this->path;
    }
}
