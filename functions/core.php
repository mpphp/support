<?php

/**
 * Undocumented function
 *
 * @param array $configs
 * @param array $controllers
 * @param array $routes
 * @param array $middleware
 * @return array
 */
function mpphp_application(array $configs, array $controllers, array $routes, array $middleware)
{ 
    return [
        'configs' => _merge_recursive($configs),
        'controllers' => $controllers,
        'routes' => _merge_recursive($routes),
        'middleware' => _merge_recursive($middleware)
    ];
}