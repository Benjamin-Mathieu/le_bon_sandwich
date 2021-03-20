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
        ],
        'cors' => [
            'methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'PATCH'],
            'allow.headers' => ['Content-Type', 'Authorization', 'Accept', 'Origin', 'X-commande-token'],
            'max.age' => 60 * 60,
            'credentials' => true
        ]
    ]
];
