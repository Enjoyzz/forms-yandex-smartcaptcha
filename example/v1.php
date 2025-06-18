<?php

declare(strict_types=1);

use Enjoys\Forms\Captcha\YandexSmartCaptcha\YandexSmartCaptcha;
use Enjoys\Forms\Form;
use Enjoys\Forms\Renderer\Html\HtmlRenderer;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;

require __DIR__ . '/../vendor/autoload.php';

try {
    $httpFactory = new HttpFactory();
    $form = new Form();
    $captcha = new YandexSmartCaptcha(
        httpClient: new Client(),
        requestFactory: $httpFactory,
        streamFactory: $httpFactory
    );
    $captcha->setPublicKey('6LdUGNEZAAAAANA5cPI_pCmOqbq-6_srRkcGOwRy');
    $captcha->setPrivateKey('6LdUGNEZAAAAAPPz685RwftPySFeCLbV1xYJJjsk');

    $form->captcha($captcha);
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
