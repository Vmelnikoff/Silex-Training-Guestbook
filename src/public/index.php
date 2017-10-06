<?php

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = new \Dotenv\Dotenv(__DIR__ . '/../../');
$dotenv->load();

$app = new Silex\Application(['debug' => getenv('APP_DEBUG')]);

$app->register(new Silex\Provider\TwigServiceProvider(), [
    'twig.path' => __DIR__ . '/../../views',
    'twig.options' => ['cache' => false],  // на продакшене установить __DIR__ . '/../../cache']
]);

$app->register(new Silex\Provider\DoctrineServiceProvider(), [
    'db.options' => [
        'driver' => getenv('DB_DRIVER'),
        'host' => getenv('DB_HOST'),
        'dbname' => getenv('DB_DATABASE'),
        'user' => getenv('DB_USERNAME'),
        'password' => getenv('DB_PASSWORD'),
        'charset' => getenv('DB_CHARSET'),
    ]
]);

require_once __DIR__ . '/../app/routes.php';

$app->run();
