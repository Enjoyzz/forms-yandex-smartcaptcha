<?php

declare(strict_types=1);

namespace Enjoys\Forms\Captcha\YandexSmartCaptcha;


use Enjoys\Forms\Element;
use Enjoys\Forms\Interfaces\CaptchaInterface;
use Enjoys\Forms\Interfaces\Ruleable;
use Enjoys\Forms\Traits\Request;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class YandexSmartCaptcha implements CaptchaInterface
{
    use Request;

    private const VERIFY_URL = 'https://smartcaptcha.yandexcloud.net/validate';
    private string $privateKey = 'secret_key';
    private string $publicKey = 'site_key';

    private ?string $ruleMessage = null;

    /**
     * @var array<string, mixed>
     */
    private array $options = [];

    public function __construct(
        private ClientInterface $httpClient,
        private RequestFactoryInterface $requestFactory,
        private StreamFactoryInterface $streamFactory,
        private ?\Closure $getUserIpCallback = null
    ) {
    }

    public function getName(): string
    {
        return 'smart-captcha';
    }

    public function getRuleMessage(): ?string
    {
        return $this->ruleMessage;
    }

    public function setRuleMessage(?string $message = null): void
    {
        $this->ruleMessage = $message;
    }

    public function renderHtml(Element $element): string
    {
        return <<<HTML
<script src="https://smartcaptcha.yandexcloud.net/captcha.js" defer></script>
<div
    id="captcha-container"
    class="smart-captcha"
    data-sitekey="$this->publicKey"
></div>
HTML;
    }

    public function validate(Ruleable $element): bool
    {
        $data = [
            'secret' => $this->privateKey,
            'token' => $this->getRequest()->getParsedBody()['smart-token']
                ?? $this->getRequest()->getQueryParams()['smart-token'] ?? null
        ];

        if ($this->getUserIpCallback !== null) {
            $data['ip'] = call_user_func($this->getUserIpCallback);
        }


        $request = $this->requestFactory
            ->createRequest('POST', YandexSmartCaptcha::VERIFY_URL)
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withBody(
                $this->streamFactory->createStream(
                    \http_build_query($data, '', '&')
                )
            );

        $response = $this->httpClient->sendRequest($request);

        $responseBody = \json_decode($response->getBody()->getContents());

        if ($responseBody->status !== 'ok') {
            /** @psalm-suppress UndefinedMethod */
            $element->setRuleError(implode(', ', [
                $responseBody->message
            ]));
            return false;
        }
        return true;
    }

    public function setPrivateKey(#[\SensitiveParameter] string $privateKey): void
    {
        $this->privateKey = $privateKey;
    }

    public function setPublicKey(string $publicKey): void
    {
        $this->publicKey = $publicKey;
    }


}
