<?php

/**
 * Generate an asset path for the application.
 *
 * @param string $asset <p>
 * The asset to be linked to
 * </p>
 * @return string
 */
function _asset(string $asset):string
{
    global $app;

    return $app['configs']['app']['url'] .'/' . $asset;
}

/**
 * Redirect to the specified location.
 *
 * @param string $to
 */
function _redirect($to = '/')
{
    header('Location: ' . $to);
    exit;
}

/**
 * Redirect to the given route if the given condition is false
 *
 * @param $condition
 * @param string $to
 */
function _redirect_else($condition, $to = '/')
{
    if (! $condition) {
        _redirect($to);
    }
}

/**
 * Return a controller action depending on the request method.
 *
 * @param array $route
 * @param string $default
 * @return mixed
 */
function _request_action(array $route, $default = 'index')
{
    foreach ($route as $method => $action) {
        if ($_SERVER['REQUEST_METHOD'] == strtoupper($method)) {
            return $action;
        }
    }

    return $default;
}

/**
 * Generate the URL to a route.
 *
 * @param string $path
 * @param array $params
 * @return string
 */
function _route($path = '', array $params = [])
{
    global $app;

    return $app['configs']['app']['url'] . "{$path}" . _query($params);
}

/**
 * Determine if the given value is "blank".
 *
 * @param  mixed  $value
 * @return bool
 */
function _blank($value)
{
    if (is_null($value)) {
        return true;
    }

    if (is_string($value)) {
        return trim($value) === '';
    }

    if (is_numeric($value) || is_bool($value)) {
        return false;
    }

    return empty($value);
}

/**
 * Wrap var_dump in a <pre> tag.
 * 
 * @param $var
 * @param bool $exit
 * @return null|void
 */
function _pretty_dump($var, $exit = false)
{
    print '<pre>';
    var_dump($var);
    print '</pre>';
    
    return $exit ? exit : null;
}