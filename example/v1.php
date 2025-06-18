<?php

declare(strict_types=1);

use Enjoys\Dotenv\Dotenv;
use Enjoys\Forms\AttributeFactory;
use Enjoys\Forms\Captcha\YandexSmartCaptcha\WidgetOptions;
use Enjoys\Forms\Captcha\YandexSmartCaptcha\YandexSmartCaptcha;
use Enjoys\Forms\Form;
use Enjoys\Forms\Renderer\Html\HtmlRenderer;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = new Dotenv(__DIR__ . '/../.env');
$dotenv->loadEnv(true);

try {
    $httpFactory = new HttpFactory();
    $form = new Form();
    $form->setId('sdfsdf');
    $captcha = new YandexSmartCaptcha(
        httpClient: new Client(),
        requestFactory: $httpFactory,
        streamFactory: $httpFactory
    );
    $captcha
        ->setPublicKey(
            $_ENV['SMARTCAPTCHA_PUBLIC_KEY'] ??
            throw new InvalidArgumentException(
                'Yandex.SmartCaptcha requires public key. Set in .env: SMARTCAPTCHA_PUBLIC_KEY'
            )
        )->setPrivateKey(
            $_ENV['SMARTCAPTCHA_SERVER_KEY'] ??
            throw new InvalidArgumentException(
                'Yandex.SmartCaptcha requires secret key. Set in .env: SMARTCAPTCHA_SERVER_KEY'
            )
        )->setWidgetOptions(
            new WidgetOptions(
            //  language: 'ru',
                invisible: true,
            //    shieldPosition: 'bottom-center',
             //   hideShield: false
            )
        );

    $form->captcha($captcha)
        ->addAttribute(AttributeFactory::create('style', 'width: 300px;'));

    $form->submit('submit');
    if ($form->isSubmitted()) {
        dump($_REQUEST);
    }
    $renderer = new HtmlRenderer($form);
    echo include __DIR__ . '/.assets.php';
    echo sprintf('<div class="container-fluid">%s</div>', $renderer->output());
} catch (Exception|Error $e) {
    echo 'Error: ' . $e->__toString();
}
