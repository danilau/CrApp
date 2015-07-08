<?php
/**
 * Created by PhpStorm.
 * User: Andrej
 * Date: 27.6.15
 * Time: 23.06
 */
return [
    'id' => 'coins-console',
    'basePath' => dirname(__DIR__),
    'components' => [
        'redis' => [
                'class' => 'yii\redis\Connection',
                'hostname' => 'localhost',
                'port' => 6379,
                'database' => 1,
            ],
        'websocket' => [
            'class' => 'app\components\WebSocketServer',
            'host' => '0.0.0.0',
            'port' => 8080,
            'redisIpHost' => '127.0.0.1'
        ]
    ],
];