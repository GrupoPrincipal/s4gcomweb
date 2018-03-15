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
            'host' => 'localhost',
            'dbname' => 'webs4gcom',
            'user' => 'root',
            'pass' => '',
            'options' => [
                \PDO::ATTR_EMULATE_PREPARES => true
            ]
        ],
        'ventor' => [
            'host' => 'localhost',
            'dbname' => 'ventoradm001',
            'user' => 'root',
            'pass' => '',
            'options' => [
                \PDO::ATTR_EMULATE_PREPARES => true
            ]
        ],
        'guia' => [
            'host' => 'localhost',
            'dbname' => 'guiacarga',
            'user' => 'root',
            'pass' => '',
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
