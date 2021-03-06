<?php

/**
 * This function will return false on new sessions or when a session 
 * is loaded by a host with a different IP address or browser. 
 * _prevent_hijacking will true if the session is valid and false otherwise. 
 * This means it will return false not just on malicious attempts 
 * but completely new sessions as well.
 *
 * @return bool
 */
function _prevent_hijacking():bool
{
	if(!isset($_SESSION['IPaddress']) || !isset($_SESSION['userAgent']))
		return false;

	if ($_SESSION['IPaddress'] != $_SERVER['REMOTE_ADDR'])
		return false;

	if( $_SESSION['userAgent'] != $_SERVER['HTTP_USER_AGENT'])
		return false;

	return true;
}

/**
 * Undocumented function
 *
 * @return void
 */
function _regenerate_session()
{
	// If this session is obsolete it means there already is a new id
	if(isset($_SESSION['OBSOLETE']) && $_SESSION['OBSOLETE'] == true)
		return;

	// Set current session to expire in 10 seconds
	$_SESSION['OBSOLETE'] = true;
	$_SESSION['EXPIRES'] = time() + 10;

	// Create new session without destroying the old one
	session_regenerate_id(false);

	// Grab current session ID and close both sessions to allow other scripts to use them
	$newSession = session_id();
	session_write_close();

	// Set session ID to the new one, and start it back up again
	session_id($newSession);
	session_start();

	// Now we unset the obsolete and expiration values for the session we want to keep
	unset($_SESSION['OBSOLETE']);
	unset($_SESSION['EXPIRES']);
}

/**
 * This function to check for the obsolete flag and to see if the session has expired.
 *
 * @return bool
 */
function _validate_session():bool
{
	if( isset($_SESSION['OBSOLETE']) && !isset($_SESSION['EXPIRES']) )
		return false;

	if(isset($_SESSION['EXPIRES']) && $_SESSION['EXPIRES'] < time())
		return false;

	return true;
}

/**
 * The default session setup is not at all secure by itself, 
 * so we’re going to create a wrapper to add the security we need.
 *
 * @param string $name
 * @param integer $lifetime <p>Lifetime of the session cookie, defined in seconds. 
 * </p>
 * @param string $path <p> Path on the domain where the cookie will work. 
 * Use a single slash ('/') for all paths on the domain. </p>
 * @param mixed $domain <p> Cookie domain, for example 'www.php.net'. 
 * To make cookies visible on all subdomains then the domain must 
 * be prefixed with a dot like '.php.net'. </p>
 * @param mixed $secure <p> If TRUE cookie will only be sent over secure connections. 
 * @param bool $http_only
 * </p>
 * 
 * @return void
 */
function _session_start($name, $lifetime = 0, $path = '/', $domain = null, $secure = null, $http_only = true)
{
	// Set the cookie name
	session_name($name . '_Session');

	// Set SSL level
	$https = isset($secure) ? $secure : isset($_SERVER['HTTPS']);

    // Set session cookie options
    // More on session_set_cookie_params visit - https://www.php.net/manual/en/function.session-set-cookie-params.php
    session_set_cookie_params(time()+$lifetime, $path, $domain, $https, $http_only);
    
	session_start();

	// Make sure the session hasn't expired, and destroy it if it has
	if(_validate_session())
	{
		// Check to see if the session is new or a hijacking attempt
		if(! _prevent_hijacking())
		{
			// Reset session data and regenerate id
			$_SESSION = array();
			$_SESSION['IPaddress'] = $_SERVER['REMOTE_ADDR'];
			$_SESSION['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
			_regenerate_session();

		// Give a 5% chance of the session id changing on any request
		}elseif(rand(1, 100) <= 5){
			_regenerate_session();
		}
	}else{
		$_SESSION = array();
		session_destroy();
		session_start();
	}
}