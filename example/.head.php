<?php

use Enjoys\Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = new Dotenv(__DIR__ . '/../.env');
$dotenv->loadEnv(true);

$clientKey = $_ENV['SMARTCAPTCHA_PUBLIC_KEY'] ??
    throw new InvalidArgumentException(
        'Yandex.SmartCaptcha requires public key. Set in .env: SMARTCAPTCHA_PUBLIC_KEY'
    );
$secretKey = $_ENV['SMARTCAPTCHA_SERVER_KEY'] ??
    throw new InvalidArgumentException(
        'Yandex.SmartCaptcha requires secret key. Set in .env: SMARTCAPTCHA_SERVER_KEY'
    );