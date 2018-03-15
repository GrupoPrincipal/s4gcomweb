<?php
return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header
        'determineRouteBeforeAppMiddleware' => true, // Allow middleware CORS

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],

        // Database settings
        'db' => [
            'host' => '192.168.64.2:3307',
            'dbname' => 'webs4gcom',
            'user' => 'estaciones',
            'pass' => '123',
            'options' => [
                \PDO::ATTR_EMULATE_PREPARES => true
            ]
        ],
        'ventor' => [
            'host' => '192.168.64.2:3307',
            'dbname' => 'ventoradm001',
            'user' => 'estaciones',
            'pass' => '123',
            'options' => [
                \PDO::ATTR_EMULATE_PREPARES => true
            ]
        ],
        'guia' => [
            'host' => '192.168.64.2:3307',
            'dbname' => 'guiacarga',
            'user' => 'estaciones',
            'pass' => '123',
            'options' => [
                \PDO::ATTR_EMULATE_PREPARES => true
            ]
        ],
        'sync' => [
            'host' => '192.168.64.2:3307',
            'dbname' => 's4gcomweb',
            'user' => 'estaciones',
            'pass' => '123',
            'options' => [
                \PDO::ATTR_EMULATE_PREPARES => true
            ]
        ],

        // Doctrine ORM
        'doctrine' => [
            'meta' => [
                'entity_path' => [
                    __DIR__.'/Entity'
                ],
                'auto_generate_proxies' => true,
                'proxy_dir' =>  __DIR__.'/../cache/proxies',
                'cache' => null,
            ],
            'connection' => [
                'driver'   => 'pdo_mysql',
                'host'     => 'localhost',
                'dbname'   => 'seuz4plu_s4gcomweb',
                'user'     => 'root',
                'password' => '18396292',

            ]
        ]
    ],
];
