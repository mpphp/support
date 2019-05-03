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
 * Return the authenticated user from the session.
 *
 * @return array|null
 */
function _auth_user()
{
    /*
     * Simple logic to make sure we have a user logged in the session.
     * If user does not exist or its empty, return null
     *
     * @reference For more on array_key_exists() visit: http://php.net/manual/en/function.array-key-exists.php
     * */
    $user = _auth_check() ? $_SESSION['authenticated']['user'] : null;

    // return user
    return  $user;
}

/**
 * Undocumented function
 *
 * @return bool
 */
function _auth_check()
{
    return ($_SESSION && array_key_exists('authenticated', $_SESSION)) 
    && (array_key_exists('user', $_SESSION['authenticated']) && ! empty($_SESSION['authenticated']['user']));
}

/**
 * Undocumented function
 *
 * @param string $key
 * @return array|null
 */
function _get_flash(string $key)
{
    return ($_SESSION && array_key_exists('flash', $_SESSION) && is_array($_SESSION['flash'])) 
        && (array_key_exists($key, $_SESSION['flash']) && ! empty($_SESSION['flash'][$key])) 
        ? $_SESSION['flash'][$key] : null;
}

/**
 * Make a nested path , creating directories down the path
 * Recursively.
 *
 * @param string $path
 * @return bool
 */
function make_path(string $path)
{
	$dir = pathinfo($path , PATHINFO_DIRNAME);
	
	if( is_dir($dir) )
	{
		return true;
	}
	else
	{
		if( make_path($dir) )
		{
			if( mkdir($dir) )
			{
				chmod($dir , 0777);
				return true;
			}
		}
	}
	
	return false;
}

/**
 * Undocumented function
 *
 * @param array $data
 * @return void
 */
function _set_flash(array $data)
{
    $_SESSION['flash'] = $data;
}

/**
 * Redirect to the specified location.
 *
 * @param string $to
 */
function _redirect($to = '/', array $with = [])
{
    if (! empty($with)) {
        _set_flash($with);
    }

    header('Location: ' . $to);
    exit;
}

/**
 * Redirect back to the previous location.
 */
function _redirect_back(array $with = [])
{
    if (! empty($with)) {
        _set_flash($with);
    }
    
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

/**
 * Redirect if the given condition is false
 *
 * @param bool $condition
 */
function _redirect_back_else($condition, array $with = [])
{
    if (! $condition) {
        if (! empty($with)) {
            _set_flash($with);
        }
        
        _redirect_back();
    }
}

/**
 * Redirect back if the given condition is true
 *
 * @param bool $condition
 */
function _redirect_back_if($condition, array $with = [])
{
    if ($condition) {
        if (! empty($with)) {
            _set_flash($with);
        }
        _redirect_back();
    }
}

/**
 * Redirect to the given route if the given condition is false
 *
 * @param $condition
 * @param string $to
 */
function _redirect_else($condition, $to = '/', array $with = [])
{
    if (! $condition) {
        if (! empty($with)) {
            _set_flash($with);
        }
        
        _redirect($to);
    }
}

/**
 * Redirect if the condition is true.
 *
 * @param $condition
 * @param string $to
 */
function _redirect_if($condition, $to = '/', array $with = [])
{
    if ($condition) {
        if (! empty($with)) {
            _set_flash($with);
        }

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
function _dump($var, $exit = false)
{
    print '<pre>';
    var_dump($var);
    print '</pre>';
    
    return $exit ? exit : null;
}

function _errors(string $key = null) {

    $errors = 
        (array_key_exists('flash', $_SESSION) && ! _blank($_SESSION['flash']))
        && (array_key_exists('errors', $_SESSION['flash']) 
        && ! empty($_SESSION['flash']['errors']))
        ? $_SESSION['flash']['errors'] : [];

    if (! _blank($key)) {
        if (! _blank($key) && array_key_exists($key, $errors)) {
            return $errors[$key];
        }

        return null;
    }

    return $errors;
}

function _old($value)
{
    $old_value = (array_key_exists('flash', $_SESSION) && ! _blank($_SESSION['flash']))
        && (array_key_exists('old', $_SESSION['flash']) 
        && ! empty($_SESSION['flash']['old']))
        ? $_SESSION['flash']['old'] : null;

    $value = ! _blank($old_value) && array_key_exists($value, $old_value) 
        ? $old_value[$value] : null;

    return htmlentities($value);
}

/**
 * Check a request method and redirect if it does not match the method given.
 *
 * @param string $method <p>
 * The method to be checked for a match.
 * This should be a "POST" or a "GET" value.
 * </p>
 */
function _check_request_method(string $method) {
    // If $_SERVER['REQUEST_METHOD'] does not matches the method option.
    if ($_SERVER['REQUEST_METHOD'] != strtoupper($method)) {

        // Set an error message.
        _set_flash(['errors' => [
            'Request method not allowed.']
            ]);

        // Redirect back.
        _redirect_back();
    }
}