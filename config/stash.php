<?php

return [
    'default_cache' => 'default',

    /*
     * enables query logging, false to disable
     */
    'tracking' => false,

    /*
     * enables query logging of full cache values, false to disable
     */
    'tracking_values' => false,


    'caches' => [
        'default' => [
            'drivers'  => [ 'FileSystem' ],
            'inMemory' => true,
        ]
    ],

    'drivers' => [
        'FileSystem' => [
            'dirSplit'          => 2,
            'path'              => '%kernel.cache_dir%/stash',
            'filePermissions'   => 0660,
            'dirPermissions'    => 0770,
            'memKeyLimit'       => 200,
            'keyHashFunction'   => 'md5',
            'encoder'           => 'Native'
        ],
        'Redis' => [
            'password' => null,
            'database' => null,
            'servers'  => [
                [
                    'server' => '127.0.0.1',
                    'port'   => '6379',
                ]
            ]
        ],
        'Memcache' => [
            'compression' => false,
            'prefix_key' => null,
            'servers' => [
                [
                    'server' => '127.0.0.1',
                    'port'   => '11211',
                    'weight' => null,
                ]
            ],
        ],
        'SQLite' => [
            'filePermissions'   => 0660,
            'dirPermissions'    => 0770,
            'busyTimeout'       => 500,
            'nesting'           => 0,
            'subhandler'        => 'PDO',
            'version'           => null,
            'path'              => '%kernel.cache_dir%/stash',
        ],
        'Apc' => [
            'ttl'               => 300,
            'namespace'         => null,
        ],
    ]
];
