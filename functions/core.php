<?php

function mpphp_application(array $configs, array $controllers, array $routes)
{ 
    return [
        'configs' => _merge_recursive($configs),
        'controllers' => $controllers,
        'routes' => _merge_recursive($routes)
    ];
}