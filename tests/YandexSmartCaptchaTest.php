<?php

declare(strict_types=1);

namespace Tests\Enjoys\Forms\Captcha\YandexSmartCaptcha;

use Enjoys\Forms\Captcha\YandexSmartCaptcha\WidgetOptions;
use Enjoys\Forms\Captcha\YandexSmartCaptcha\YandexSmartCaptcha;
use Enjoys\Forms\Elements\Captcha;
use Enjoys\Forms\Form;
use Enjoys\Forms\Interfaces\Ruleable;
use Enjoys\Session\Session;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

class YandexSmartCaptchaTest extends TestCase
{
    private RequestFactoryInterface $requestFactory;
    private StreamFactoryInterface $streamFactory;
    private ServerRequestInterface $request;
    private YandexSmartCaptcha $captcha;
    private MockHandler $mockHandler;
    private Client $httpClient;
    private Form $form;

    protected function setUp(): void
    {
        $this->form = new Form(id: 'test-form', session: $this->createMock(Session::class));
        // Создаем мок-обработчик для Guzzle
        $this->mockHandler = new MockHandler();
        $this->httpClient = new Client(['handler' => HandlerStack::create($this->mockHandler)]);

        $this->requestFactory =  $this->streamFactory =  new HttpFactory();

        $this->captcha = new YandexSmartCaptcha(
            $this->httpClient,
            $this->requestFactory,
            $this->streamFactory
        );

        $this->request = $this->createMock(ServerRequestInterface::class);
    }


    public function testRenderHtml(): void
    {
        $this->captcha->setWidgetOptions(new WidgetOptions(callback: 'myCallback'));
        $element = new Captcha($this->captcha);
        $element->setForm($this->form);

        $result = $this->captcha->renderHtml($element);

        $this->assertStringContainsString('https://smartcaptcha.yandexcloud.net/captcha.js', $result);
        $this->assertStringContainsString('<div', $result);
        $this->assertStringContainsString('id="captcha-container"', $result);
        $this->assertStringContainsString('test-form', $result);
        $this->assertStringContainsString('"callback":"myCallback"', $result);
        $this->assertStringNotContainsString('"invisible":null', $result);
    }

    public function testRenderHtmlThrowsExceptionWhenFormIdIsNull(): void
    {
        $this->form->setId(null);
        $element = (new Captcha($this->captcha))->setForm($this->form);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The Form Id cannot be null');

        $this->captcha->renderHtml($element);
    }

    public function testRenderHtmlWithInvisibleCaptcha(): void
    {
        $widgetOptions = new WidgetOptions(callback: 'myCallback', invisible: true);
        $this->captcha->setWidgetOptions($widgetOptions);

        $element = (new Captcha($this->captcha))->setForm($this->form);

        $result = $this->captcha->renderHtml($element);

        $this->assertStringContainsString('"invisible":true', $result);
        $this->assertStringContainsString('"callback":"invisibleCallbackProcess"', $result);
        $this->assertStringContainsString('const invisible = true', $result);
    }

    public function testValidateWhenTokenMissing(): void
    {

        $this->request->method('getParsedBody')->willReturn([]);
        $this->request->method('getQueryParams')->willReturn([]);

        $this->captcha->setRequest($this->request);

        $element = (new Captcha($this->captcha))->setForm($this->form);

        $this->assertFalse($this->captcha->validate($element));
        $this->assertSame('Smart token is missing', $element->getRuleErrorMessage());
    }

    public function testValidateWhenApiReturnsError(): void
    {
        $this->request->method('getParsedBody')->willReturn(['smart-token' => 'test-token']);
        $this->captcha->setRequest($this->request);

        $this->mockHandler->append(new Response(400, [], '{"status":"error","message":"Invalid token"}'));

        $element = (new Captcha($this->captcha))->setForm($this->form);

        $this->assertFalse($this->captcha->validate($element));
        $this->assertStringContainsString('Invalid token', $element->getRuleErrorMessage());
    }

    public function testValidateWhenApiReturnsOk(): void
    {
        $this->request->method('getParsedBody')->willReturn(['smart-token' => 'test-token']);
        $this->captcha->setRequest($this->request);

        $element = (new Captcha($this->captcha))->setForm($this->form);


        $this->mockHandler->append(new Response(200, [], '{"status":"ok"}'));

        $this->assertTrue($this->captcha->validate($element));
    }

    public function testValidateWithNetworkError(): void
    {
        $this->request->method('getParsedBody')->willReturn(['smart-token' => 'test-token']);
        $this->captcha->setRequest($this->request);

        $element = (new Captcha($this->captcha))->setForm($this->form);

        // Симулируем сетевую ошибку
        $this->mockHandler->append(
            new RequestException(
                "Network error",
                $this->createMock(RequestInterface::class)
            )
        );

        $this->assertFalse($this->captcha->validate($element));
        $this->assertStringContainsString('Network error during captcha verification: Network error', $element->getRuleErrorMessage());
    }

    public function testValidateWithInvalidJson(): void
    {
        $this->request->method('getParsedBody')->willReturn(['smart-token' => 'test-token']);
        $this->captcha->setRequest($this->request);
        // Возвращаем невалидный JSON
        $this->mockHandler->append(new Response(200, [], 'invalid-json}'));

        $element = (new Captcha($this->captcha))->setForm($this->form);

        $this->assertFalse($this->captcha->validate($element));
        $this->assertStringContainsString(
            'Invalid response from captcha service: Syntax error',
            $element->getRuleErrorMessage()
        );
    }

    public function testValidateWithUserIpCallback(): void
    {
        $captcha = new YandexSmartCaptcha(
            $this->httpClient,
            $this->requestFactory,
            $this->streamFactory,
            fn() => '192.168.0.1'
        );

        $this->request->method('getParsedBody')->willReturn(['smart-token' => 'test-token']);
        $captcha->setRequest(request: $this->request);

        // Настраиваем перехват запроса для проверки параметров
        $this->mockHandler->append(function (RequestInterface $request) {
            // Проверяем параметры запроса
            parse_str($request->getBody()->getContents(), $params);
            $this->assertEquals('192.168.0.1', $params['ip']);
            $this->assertEquals('secret_key', $params['secret']);
            $this->assertEquals('test-token', $params['token']);

            return new Response(200, [], '{"status":"ok"}');
        });

        $element = (new Captcha($captcha))->setForm($this->form);


        $this->assertTrue($captcha->validate($element));
    }

    public function testPublicKeyAffectsRendering(): void
    {
        $this->captcha->setPublicKey('test_public_key');

        $element = (new Captcha($this->captcha))->setForm($this->form);
        $html = $this->captcha->renderHtml($element);

        $this->assertStringContainsString('"sitekey":"test_public_key"', $html);
    }

    public function testPrivateKeyAffectsValidation(): void
    {
        $this->captcha->setPrivateKey('test_private_key');

        $this->request->method('getParsedBody')->willReturn(['smart-token' => 'test-token']);
        $this->captcha->setRequest($this->request);

        $this->mockHandler->append(function (RequestInterface $request) {
            parse_str($request->getBody()->getContents(), $params);
            $this->assertSame('test_private_key', $params['secret']);
            return new Response(200, [], '{"status":"ok"}');
        });

        $element = (new Captcha($this->captcha))->setForm($this->form);
        $this->assertTrue($this->captcha->validate($element));
    }
}