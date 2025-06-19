<?php

declare(strict_types=1);

use Enjoys\Forms\Captcha\YandexSmartCaptcha\Language;
use Enjoys\Forms\Captcha\YandexSmartCaptcha\ShieldPosition;
use Enjoys\Forms\Captcha\YandexSmartCaptcha\WidgetOptions;
use Enjoys\Forms\Captcha\YandexSmartCaptcha\YandexSmartCaptcha;
use Enjoys\Forms\Form;
use Enjoys\Forms\Renderer\Html\HtmlRenderer;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;


include __DIR__ . '/.head.php';

try {
    $httpFactory = new HttpFactory();
    $form = new Form();
    $form->setId('invisible_captcha');
    $captcha = new YandexSmartCaptcha(
        httpClient: new Client(),
        requestFactory: $httpFactory,
        streamFactory: $httpFactory
    );

    /** @var string $clientKey */
    /** @var string $secretKey */
    $captcha->setPublicKey($clientKey)
        ->setPrivateKey($secretKey)
        ->setWidgetOptions(
            new WidgetOptions(
                hl: Language::RU,
                callback: 'myCallback',
                invisible: true,
                shieldPosition: ShieldPosition::CENTER_LEFT,
                test: true
            )
        );

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
