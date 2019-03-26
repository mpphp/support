<?php

/**
 * Determine whether the given value is array accessible.
 *
 * @param  mixed  $value
 * @return bool
 */
function _accessible($value)
{
    return is_array($value);
}

/**
 * Add an element to an array using "_dot" notation if it doesn't exist.
 *
 * @param  array   $array
 * @param  string  $key
 * @param  mixed   $value
 * @return array
 */
function _add($array, $key, $value)
{
    if (is_null(_get($array, $key))) {
        _set($array, $key, $value);
    }

    return $array;
}

/**
 * Collapse an array of arrays into a single array.
 *
 * @param  array  $array
 * @return array
 */
function _collapse($array)
{
    $results = [];

    foreach ($array as $values) {
        if (! is_array($values)) {
            continue;
        }

        $results = array_merge($results, $values);
    }

    return $results;
}

/**
 * Cross join the given arrays, returning all possible permutations.
 *
 * @param  array  ...$arrays
 * @return array
 */
function _crossJoin(...$arrays)
{
    $results = [[]];

    foreach ($arrays as $index => $array) {
        $append = [];

        foreach ($results as $product) {
            foreach ($array as $item) {
                $product[$index] = $item;

                $append[] = $product;
            }
        }

        $results = $append;
    }

    return $results;
}

/**
 * Get an item from an array or object using "_dot" notation.
 *
 * @param  mixed   $target
 * @param  string|array  $key
 * @param  mixed   $default
 * @return mixed
 */
function _data_get($target, $key, $default = null)
{
    if (is_null($key)) {
        return $target;
    }

    $key = is_array($key) ? $key : explode('.', $key);

    while (! is_null($segment = array_shift($key))) {
        if ($segment === '*') {
            if (! is_array($target)) {
                return _value($default);
            }

            $result = [];

            foreach ($target as $item) {
                $result[] = _data_get($item, $key);
            }

            return in_array('*', $key) ? _collapse($result) : $result;
        }

        if (_accessible($target) && _exists($target, $segment)) {
            $target = $target[$segment];
        } elseif (is_object($target) && isset($target->{$segment})) {
            $target = $target->{$segment};
        } else {
            return _value($default);
        }
    }

    return $target;
}

/**
 * Set an item on an array or object using _dot notation.
 *
 * @param  mixed  $target
 * @param  string|array  $key
 * @param  mixed  $value
 * @param  bool  $overwrite
 * @return mixed
 */
function _data_set(&$target, $key, $value, $overwrite = true)
{
    $segments = is_array($key) ? $key : explode('.', $key);

    if (($segment = array_shift($segments)) === '*') {
        if (! _accessible($target)) {
            $target = [];
        }

        if ($segments) {
            foreach ($target as &$inner) {
                _data_set($inner, $segments, $value, $overwrite);
            }
        } elseif ($overwrite) {
            foreach ($target as &$inner) {
                $inner = $value;
            }
        }
    } elseif (_accessible($target)) {
        if ($segments) {
            if (! _exists($target, $segment)) {
                $target[$segment] = [];
            }

            _data_set($target[$segment], $segments, $value, $overwrite);
        } elseif ($overwrite || ! _exists($target, $segment)) {
            $target[$segment] = $value;
        }
    } else {
        $target = [];

        if ($segments) {
            _data_set($target[$segment], $segments, $value, $overwrite);
        } elseif ($overwrite) {
            $target[$segment] = $value;
        }
    }

    return $target;
}

/**
 * Divide an array into two arrays. One with keys and the other with values.
 *
 * @param  array  $array
 * @return array
 */
function _divide($array)
{
    return [array_keys($array), array_values($array)];
}

/**
 * Flatten a multi-dimensional associative array with dots.
 *
 * @param  array   $array
 * @param  string  $_prepend
 * @return array
 */
function _dot($array, $_prepend = '')
{
    $results = [];

    foreach ($array as $key => $value) {
        if (is_array($value) && ! empty($value)) {
            $results = array_merge($results, _dot($value, $_prepend.$key.'.'));
        } else {
            $results[$_prepend.$key] = $value;
        }
    }

    return $results;
}

/**
 * Get all of the given array except for a specified array of keys.
 *
 * @param  array  $array
 * @param  array|string  $keys
 * @return array
 */
function _except($array, $keys)
{
    _forget($array, $keys);

    return $array;
}

/**
 * Determine if the given key exists in the provided array.
 *
 * @param  array  $array
 * @param  string|int  $key
 * @return bool
 */
function _exists($array, $key)
{
    return array_key_exists($key, $array);
}

/**
 * Check if file exists in the given locations.
 * Return file full path or return false
 *
 * @param string $file
 * @param array $locations
 * @return bool|string
 */
function _file_search(string $file, array $locations)
{
    foreach ($locations as $location) {
        if (file_exists($location.$file)) {
            return $location.$file;
        }
    }

    return false;
}

/**
 * Return the first element in an array passing a given truth test.
 *
 * @param  array  $array
 * @param  callable|null  $callback
 * @param  mixed  $default
 * @return mixed
 */
function _first($array, callable $callback = null, $default = null)
{
    if (is_null($callback)) {
        if (empty($array)) {
            return _value($default);
        }

        foreach ($array as $item) {
            return $item;
        }
    }

    foreach ($array as $key => $value) {
        if (call_user_func($callback, $value, $key)) {
            return $value;
        }
    }

    return _value($default);
}

/**
 * Flatten a multi-dimensional array into a single level.
 *
 * @param  array  $array
 * @param  int  $depth
 * @return array
 */
function _flatten($array, $depth = INF)
{
    $result = [];

    foreach ($array as $item) {
        if (! is_array($item)) {
            $result[] = $item;
        } elseif ($depth === 1) {
            $result = array_merge($result, array_values($item));
        } else {
            $result = array_merge($result, _flatten($item, $depth - 1));
        }
    }

    return $result;
}

/**
 * Remove one or many array items from a given array using "_dot" notation.
 *
 * @param  array  $array
 * @param  array|string  $keys
 * @return void
 */
function _forget(&$array, $keys)
{
    $original = &$array;

    $keys = (array) $keys;

    if (count($keys) === 0) {
        return;
    }

    foreach ($keys as $key) {
        // if the exact key _exists in the top-level, remove it
        if (_exists($array, $key)) {
            unset($array[$key]);

            continue;
        }

        $parts = explode('.', $key);

        // clean up before each pass
        $array = &$original;

        while (count($parts) > 1) {
            $part = array_shift($parts);

            if (isset($array[$part]) && is_array($array[$part])) {
                $array = &$array[$part];
            } else {
                continue 2;
            }
        }

        unset($array[array_shift($parts)]);
    }
}

/**
 * Get an item from an array using "_dot" notation.
 *
 * @param  array  $array
 * @param  string  $key
 * @param  mixed   $default
 * @return mixed
 */
function _get($array, $key, $default = null)
{
    if (! _accessible($array)) {
        return _value($default);
    }

    if (is_null($key)) {
        return $array;
    }

    if (_exists($array, $key)) {
        return $array[$key];
    }

    if (strpos($key, '.') === false) {
        return $array[$key] ?? _value($default);
    }

    foreach (explode('.', $key) as $segment) {
        if (_accessible($array) && _exists($array, $segment)) {
            $array = $array[$segment];
        } else {
            return _value($default);
        }
    }

    return $array;
}

/**
 * Check if an item or items exist in an array using "_dot" notation.
 *
 * @param  array  $array
 * @param  string|array  $keys
 * @return bool
 */
function _has($array, $keys)
{
    if (is_null($keys)) {
        return false;
    }

    $keys = (array) $keys;

    if (! $array) {
        return false;
    }

    if ($keys === []) {
        return false;
    }

    foreach ($keys as $key) {
        $subKeyArray = $array;

        if (_exists($array, $key)) {
            continue;
        }

        foreach (explode('.', $key) as $segment) {
            if (_accessible($subKeyArray) && _exists($subKeyArray, $segment)) {
                $subKeyArray = $subKeyArray[$segment];
            } else {
                return false;
            }
        }
    }

    return true;
}

/**
 * Determines if an array is associative.
 *
 * An array is "associative" if it doesn't have sequential numerical keys beginning with zero.
 *
 * @param  array  $array
 * @return bool
 */
function _is_ssoc(array $array)
{
    $keys = array_keys($array);

    return array_keys($keys) !== $keys;
}

/**
 * Get a subset of the items from the given array.
 *
 * @param  array  $array
 * @param  array|string  $keys
 * @return array
 */
function _only($array, $keys)
{
    return array_intersect_key($array, array_flip((array) $keys));
}

/**
 * Pluck an array of values from an array.
 *
 * @param  array  $array
 * @param  string|array  $value
 * @param  string|array|null  $key
 * @return array
 */
function _pluck($array, $value, $key = null)
{
    $results = [];

    [$value, $key] = _explode_pluck_parameters($value, $key);

    foreach ($array as $item) {
        $itemValue = _data_get($item, $value);

        // If the key is "null", we will just append the value to the array and keep
        // looping. Otherwise we will key the array using the value of the key we
        // received from the developer. Then we'll return the final array form.
        if (is_null($key)) {
            $results[] = $itemValue;
        } else {
            $itemKey = _data_get($item, $key);

            if (is_object($itemKey) && method_exists($itemKey, '__toString')) {
                $itemKey = (string) $itemKey;
            }

            $results[$itemKey] = $itemValue;
        }
    }

    return $results;
}

/**
 * Explode the "value" and "key" arguments passed to "_pluck".
 *
 * @param  string|array  $value
 * @param  string|array|null  $key
 * @return array
 */
function _explode_pluck_parameters($value, $key)
{
    $value = is_string($value) ? explode('.', $value) : $value;

    $key = is_null($key) || is_array($key) ? $key : explode('.', $key);

    return [$value, $key];
}

/**
 * Return the _last element in an array passing a given truth test.
 *
 * @param  array  $array
 * @param  callable|null  $callback
 * @param  mixed  $default
 * @return mixed
 */
function _last($array, callable $callback = null, $default = null)
{
    if (is_null($callback)) {
        return empty($array) ? _value($default) : end($array);
    }

    return _first(array_reverse($array, true), $callback, $default);
}

/**
 * @param array $arrays
 * @return array
 */
function _merge_recursive(array $arrays):array
{
    $merged = [];

    foreach ($arrays as $array) {
        $merged = array_merge_recursive($merged, $array);
    }

    return $merged;
}

/**
 * Push an item onto the beginning of an array.
 *
 * @param  array  $array
 * @param  mixed  $value
 * @param  mixed  $key
 * @return array
 */
function _prepend($array, $value, $key = null)
{
    if (is_null($key)) {
        array_unshift($array, $value);
    } else {
        $array = [$key => $value] + $array;
    }

    return $array;
}

/**
 * Get a value from the array, and remove it.
 *
 * @param  array   $array
 * @param  string  $key
 * @param  mixed   $default
 * @return mixed
 */
function _pull(&$array, $key, $default = null)
{
    $value = _get($array, $key, $default);

    _forget($array, $key);

    return $value;
}

/**
 * Get one or a specified number of random values from an array.
 *
 * @param  array  $array
 * @param  int|null  $number
 * @return mixed
 *
 * @throws \InvalidArgumentException
 */
function _random($array, $number = null)
{
    $requested = is_null($number) ? 1 : $number;

    $count = count($array);

    if ($requested > $count) {
        throw new InvalidArgumentException(
            "You requested {$requested} items, but there are _only {$count} items available."
        );
    }

    if (is_null($number)) {
        return $array[array_rand($array)];
    }

    if ((int) $number === 0) {
        return [];
    }

    $keys = array_rand($array, $number);

    $results = [];

    foreach ((array) $keys as $key) {
        $results[] = $array[$key];
    }

    return $results;
}

/**
 * Set an array item to a given value using "_dot" notation.
 *
 * If no key is given to the method, the entire array will be replaced.
 *
 * @param  array   $array
 * @param  string  $key
 * @param  mixed   $value
 * @return array
 */
function _set(&$array, $key, $value)
{
    if (is_null($key)) {
        return $array = $value;
    }

    $keys = explode('.', $key);

    while (count($keys) > 1) {
        $key = array_shift($keys);

        // If the key doesn't exist at this depth, we will just create an empty array
        // to hold the next value, allowing us to create the arrays to hold final
        // values at the correct depth. Then we'll keep digging into the array.
        if (! isset($array[$key]) || ! is_array($array[$key])) {
            $array[$key] = [];
        }

        $array = &$array[$key];
    }

    $array[array_shift($keys)] = $value;

    return $array;
}

/**
 * Recursively sort an array by keys and values.
 *
 * @param  array  $array
 * @return array
 */
function _sort_recursive($array)
{
    foreach ($array as &$value) {
        if (is_array($value)) {
            $value = _sort_recursive($value);
        }
    }

    if (_is_ssoc($array)) {
        ksort($array);
    } else {
        sort($array);
    }

    return $array;
}

/**
 * Convert the array into a query string.
 *
 * @param  array  $array
 * @return string
 */
function _query($array)
{
    return http_build_query($array, null, '&', PHP_QUERY_RFC3986);
}

/**
 * Return the default value of the given value.
 *
 * @param  mixed  $value
 * @return mixed
 */
function _value($value)
{
    return $value instanceof Closure ? $value() : $value;
}

/**
 * Filter the array using the given callback.
 *
 * @param  array  $array
 * @param  callable  $callback
 * @return array
 */
function _where($array, callable $callback)
{
    return array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
}

/**
 * If the given value is not an array and not null, wrap it in one.
 *
 * @param  mixed  $value
 * @return array
 */
function _wrap($value)
{
    if (is_null($value)) {
        return [];
    }

    return is_array($value) ? $value : [$value];
}
