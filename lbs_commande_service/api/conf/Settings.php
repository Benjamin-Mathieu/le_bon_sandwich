<?php

return [
    "settings" => [
        'displayErrorDetails' => true,
        'determineRouteBeforeMiddleware' => true,
        'db' => [
            'driver' => 'mysql',
            'host' => 'dbcom',
            'database' => 'command_lbs',
            'username' => 'command_lbs',
            'password' => 'command_lbs',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ]
    ]
];
