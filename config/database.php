<?php
return ['default'=>env('DB_CONNECTION','mysql'),
'connections'=>
['mysql' => [
    'driver'    => 'mysql',
    'host'      => env('DB_HOST', '127.0.0.1'),
    'port'      => env('DB_PORT', '3306'),
    'database'  => env('DB_DATABASE', 'defaultdb'),
    'username'  => env('DB_USERNAME', 'avnadmin'),
    'password'  => env('DB_PASSWORD', ''),
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix'    => '',
    'strict'    => true,
    'engine'    => null,
    'options'   => [
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ],
],]];
