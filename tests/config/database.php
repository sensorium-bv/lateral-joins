<?php

return [
    'pgsql' => [
        'driver' => 'pgsql',
        'host' => 'postgres-timescale',
        'port' => '5432',
        'database' => 'lateral_join',
        'username' => 'default',
        'password' => 'secret',
        'charset' => 'utf8',
        'prefix' => '',
        'schema' => 'public',
        'sslmode' => 'prefer',
    ],
];