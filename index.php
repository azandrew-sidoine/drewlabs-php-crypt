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

use Drewlabs\Crypt\HMAC\Hash as HMACHash;

require_once __DIR__.'/vendor/autoload.php';

$hash = new HMACHash();
$hash->make('Hello World!');
var_dump(sprintf('Hash: %s', (string) $hash));
