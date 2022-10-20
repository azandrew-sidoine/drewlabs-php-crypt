# Crypt

Crypt is PHP composer based package providing hashing and encryption classes, and methods, based on PHP default hashing & encryption methods.

## Installation

Simply use `composer` PHP dependencies manager to install the package. If there is no composer installation on your operating system, you should be able to install the utility using this guide [https://getcomposer.org/download/].

Once composer is intalled on your operating system, run the comand below in your terminal at the root of your project:

> composer require drewlabs/crypt

The above command will add `drewlabs/crypt` package and it dependencies to your project.

## Usage

### Encrypting a raw string

```php
use Drewlabs\Crypt\Encrypter\Crypt;

// Creates a crypt instance
$instance = Crypt::new();
$encrypted = $instance->encryptString('Raw string value');
```

**Note**
By default `Crypt::new()` generate a random key and use `AES-128-CBC` as cipher type. To override the defaults:

```php
use Drewlabs\Crypt\Encrypter\Crypt;

// Creates a crypt instance
$instance = Crypt::new('MySecret');
```

**Note**
Supported cipher type are:

* `aes-128-cbc` and `aes-128-gcm` -> Key length equals to 8 characters
* `aes-256-cbc` and `aes-256-gcm` -> Key length equals 16 characters

**Note**
If the key is a base64 string, crypt library will try to decode the base64 string before creating the encryption key internally:

```php
use Drewlabs\Crypt\Encrypter\Crypt;

// Creates a crypt instance
$instance = Crypt::new(base64_encode('MySecret'));
```

* Decrypting a string

To get the plain text from an encrypted string, simply call the `decryptString()` method on the encrypted text:

```php
use Drewlabs\Crypt\Encrypter\Crypt;

// Creates a crypt instance
$instance = Crypt::new();
$encrypted = $instance->encryptString('Raw string value');

// Decrypting text
$plainText = $instance->decryptString($encrypted); // Raw string value
```

* File encryption

The `Crypt` also class provides methods/functions for entrypting an entire file and decrypt the file from back to it original state. Below are the API for encrypting and decrypting files on a disk:

+ encryptBlob(string $from, string $to): void // Encrypt document located at path `$from` and output the encrypted content to path `$to`

+ decryptBlob(string $from. string $to): void // Decrypt document located at path `$from` and output the encrypted content to path `$to`

### HMAC Hashing

HMAC hashing provides methods for creating hash and checking a raw value against a hash using user defined algorightm. To create a hash value:

```php
use Drewlabs\Crypt\HMAC\Hash as HMACHash;

$instance = HMACHash::new();
$hash = $instance->make('My Hashable Value');
```

**Note**
By default, hashed value are created from `strings`. But the Hmac implementation supports PHP arrays, and serializable objects (classes having a `toArray()`). If the object does not have a `toArray()` method, the hashing implementation call `get_object_vars` on the object to convert the object into array, before hashing it.

**Note**
To create a hash object from existing raw hashed string use the `Hash::raw()` method:

```php
use Drewlabs\Crypt\HMAC\Hash as HMACHash;

$instance = HMACHash::raw("...."); // Creates a hash instance from a raw sstring composed
```

* Checking a hashed value

To check a hashed value against a new plain text value, first you create the hash object from raw hashed value and then you call the `check()` method against the plain text.

```php
use Drewlabs\Crypt\HMAC\Hash as HMACHash;

$hash = HMACHash::new('md5');
// Returns a boolean indicating whether hashed value and plain text matches.
$boolean = $hash::raw("Hashed value")->check('Hello World!');
```

**Note**
By default the library uses `sha256` algorightm when creating hash values. PHP base function `hash_algos` returns the list of supported hash algorithm.

**Note**
Below is the api for hashing a value:

+ make(?string $alg, ?string $key = null) // Creates a hashed value using user provided algorithm
+ hashOptions() // Returns an encoded string composed of has key and algorithm used when hashing a value
+ static raw(string $hash, ?string $options = null) // Static method for creating a hash object from a raw hashed value
+ check(string $value) // Check the hash object internal hash value against a plain text value

### Password encryption

The package also comes with implementation for creating `md5`, `argon2`, `argon2i` and `bcrypt` hash from plain text. It internally uses PHP `password_hash` function for creating hash. They are recommended for hashing password for user applications. To create a password hash from plain text:

* Hash manager factory

```php
use Drewlabs\Crypt\Passwords\Factory;

// Creates a hash manager from using argon2i password hasing
$hash = Factory::new()->make(\PASSWORD_ARGON2I)
                ->resolve()
                ->make('SuperSecret');
```

* Proxy function API

```php
use function Drewlabs\Crypt\Proxy\usePasswordManager;

// Creates a hash manager from using bcrypt password hasing
$hash = usePasswordManager('bcrypt')->make('SuperSecret');
```
