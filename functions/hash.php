<?php

/**
 * Hash the given value.
 *
 * @param  string  $value <p>
 * The value to be hashed.
 * </p>
 * @param  array   $options
 * @return string
 */
function bcrypt_hasher_make($value, array $options = [])
{
    /*
     * Creates a password hash using bcrypt algorithm.
     * @reference For more on password_hash() visit: http://www.php.net/manual/en/function.password-hash.php
     * */
    $hash = password_hash($value, PASSWORD_BCRYPT, [
        'cost' => bcrypt_hasher_cost($options),
    ]);

    // If hashing failed, die with a fail message.
    if ($hash === false) {
        die('Bcrypt hashing not supported.');
    }

    // return hash.
    return $hash;
}

/**
 * Check the given plain value against a hash.
 *
 * @param  string  $value <p>
 * The value to be checked against a hash.
 * </p>
 * @param  string  $hashedValue <p>
 * The hashed string to be checked against a value
 * </p>
 * @return bool
 */
function bcrypt_hasher_check($value, $hashedValue)
{
    // If an empty string is provided.
    if (strlen($hashedValue) === 0) {
        // Return FALSE.
        return false;
    }

    /*
     * Checks if the given hash matches the given options.
     * @reference For more on password_verify() visit: http://www.php.net/manual/en/function.password-verify.php
     * */
    return password_verify($value, $hashedValue);
}

/**
 * Check if the given hash has been hashed using the given options.
 *
 * @param  string  $hashedValue <p>
 *  A hash created by bcrypt_hasher_make()
 * @param  array   $options
 * @return bool
 */
function bcrypt_hasher_needs_rehash($hashedValue, array $options = [])
{
    /*
     * Rehash value
     *
     * @reference For more on password_needs_rehash() visit: password_needs_rehash
     * */
    return password_needs_rehash($hashedValue, PASSWORD_BCRYPT, [
        'cost' => bcrypt_hasher_cost($options),
    ]);
}

/**
 * Extract the cost value from the options array.
 *
 * @param  array  $options
 * @return int
 */
function bcrypt_hasher_cost(array $options = [])
{
    /**
     * Default crypt cost factor.
     *
     * @var int
     */
    $rounds = 10;

    return isset($options['rounds']) ? $options['rounds'] : $rounds;
}