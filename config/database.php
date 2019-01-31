<?php
/* Get Site Mode from ENV */
$siteMode = env('SITE_MODE', 0);

if ($siteMode == 2 || $siteMode == 4) {
    /**
     * Production or Pre-Production
     */
    $configuration = [
        'default' => env('DB_CONNECTION', ''),
        'connections' => [
            'mysql' => [
                'driver' => 'mysql',
                'read'      => [
                    'host'  => DB_ROUTER_READ,
                    'port'  => DB_ROUTER_READ_PORT,
                ],
                'write'     => [
                    'host'  => DB_ROUTER_READ_WRITE,
                    'port'  => DB_ROUTER_READ_WRITE_PORT,
                ],
                'database' => DB_DATABASE,
                'username' => DB_USERNAME,
                'password' => DB_PASSWORD,
                'unix_socket' => env('DB_SOCKET', ''),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'prefix_indexes' => true,
                'strict' => true,
                'engine' => null,
            ]
        ],
        'migrations' => 'migrations',
        'redis' => [

            'client' => 'predis',

            'default' => [
                'host' => REDIS_HOST,
                'password' => REDIS_PASSWORD,
                'port' => REDIS_PORT,
                'database' => REDIS_DB,
            ],

            'cache' => [
                'host' => REDIS_HOST,
                'password' => REDIS_PASSWORD,
                'port' => REDIS_PORT,
                'database' => REDIS_DB,
            ],

        ],

    ];
} elseif($siteMode == 1 || $siteMode == 5) {
    /**
     * Gamma or Beta
     */
    $configuration = [
        'default' => env('DB_CONNECTION', ''),
        'connections' => [
            'mysql' => [
                'driver' => 'mysql',
               'read'      => [
                    'host'  => DB_ROUTER_READ,
                    'port'  => DB_ROUTER_READ_PORT,
                ],
                'write'     => [
                    'host'  => DB_ROUTER_READ_WRITE,
                    'port'  => DB_ROUTER_READ_WRITE_PORT,
                ],
                'database' => DB_DATABASE,
                'username' => DB_USERNAME,
                'password' => DB_PASSWORD,
                'unix_socket' => env('DB_SOCKET', ''),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'prefix_indexes' => true,
                'strict' => true,
                'engine' => null,
            ]
        ],
        'migrations' => 'migrations',
        'redis' => [

            'client' => 'predis',

            'default' => [
                'host' => REDIS_HOST,
                'password' => REDIS_PASSWORD, 
                'port' => REDIS_PORT,
                'database' => REDIS_DB
            ],

            'cache' => [
                'host' => REDIS_HOST,
                'password' => REDIS_PASSWORD, 
                'port' => REDIS_PORT,
                'database' => REDIS_DB
            ],

        ],

    ];
} else {
    /**
     * Alpha or Local
     */
    $configuration = [
        'default' => env('DB_CONNECTION', ''),

        'connections' => [
            'mysql' => [
                'driver' => 'mysql',
                'host' => env('DB_HOST', ''),
                'port' => env('DB_PORT', ''),
                'database' => env('DB_DATABASE', ''),
                'username' => env('DB_USERNAME', ''),
                'password' => env('DB_PASSWORD', ''),
                'unix_socket' => env('DB_SOCKET', ''),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'prefix_indexes' => true,
                'strict' => true,
                'engine' => null,
            ]
        ],

        'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

        'redis' => [

            'client' => 'predis',
            /*
            'default' => [
                [
                    'scheme' => env('REDIS_SCHEME', ''),
                    'host'   => env('REDIS_HOST1', ''),,
                    'port'   => env('REDIS_MASTER_PORT', ''),,
                    'password' => env('REDIS_PASSWORD', null),
                    'alias' => 'master'
                ],
                [
                    'scheme' => env('REDIS_SCHEME', ''),
                    'host'   => env('REDIS_HOST2', ''),,
                    'port'   => env('REDIS_SLAVE_PORT_1', ''),,
                    'password' => env('REDIS_PASSWORD', null),
                ],
                [
                    'scheme' => env('REDIS_SCHEME', ''),
                    'host'   => env('REDIS_HOST3', ''),,
                    'port'   => env('REDIS_SLAVE_PORT_2', ''),,
                    'password' => env('REDIS_PASSWORD', null),
                ],
                [
                    'replication' => true
                ]
            ],

            'cache' => [
                [
                    'scheme' => env('REDIS_SCHEME', ''),
                    'host'   => env('REDIS_HOST1', ''),,
                    'port'   => env('REDIS_MASTER_PORT', ''),,
                    'password' => env('REDIS_PASSWORD', null),
                    'alias' => 'master'
                ],
                [
                    'scheme' => env('REDIS_SCHEME', ''),
                    'host'   => env('REDIS_HOST2', ''),,
                    'port'   => env('REDIS_SLAVE_PORT_1', ''),,
                    'password' => env('REDIS_PASSWORD', null),
                ],
                [
                    'scheme' => env('REDIS_SCHEME', ''),
                    'host'   => env('REDIS_HOST3', ''),,
                    'port'   => env('REDIS_SLAVE_PORT_2', ''),,
                    'password' => env('REDIS_PASSWORD', null),
                ],
                [
                    'replication' => true
                ]
            ],
            */

            'default' => [
                'scheme' => env('REDIS_SCHEME', ''),
                'host' => env('REDIS_HOST', ''),
                'password' => env('REDIS_PASSWORD', null),
                'port' => env('REDIS_PORT', ''),
                'database' => env('REDIS_DB', 0),
            ],
            'cache' => [
                'scheme' => env('REDIS_SCHEME', ''),
                'host' => env('REDIS_HOST', ''),
                'password' => env('REDIS_PASSWORD', null),
                'port' => env('REDIS_PORT', ''),
                'database' => env('REDIS_CACHE_DB', 1),
            ],
        ],

    ];
    
}

return $configuration;
