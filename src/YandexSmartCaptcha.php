<?php

declare(strict_types=1);

namespace Enjoys\Forms\Captcha\YandexSmartCaptcha;


use Closure;
use Enjoys\Forms\AttributeFactory;
use Enjoys\Forms\Element;
use Enjoys\Forms\Interfaces\CaptchaInterface;
use Enjoys\Forms\Interfaces\Ruleable;
use Enjoys\Forms\Traits\Request;
use Exception;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class YandexSmartCaptcha implements CaptchaInterface
{
    use Request;

    private const VERIFY_URL = 'https://smartcaptcha.yandexcloud.net/validate';
    private string $privateKey = 'secret_key';
    private string $publicKey = 'site_key';
    /**
     * 'ru' | 'en' | 'be' | 'kk' | 'tt' | 'uk' | 'uz' | 'tr'
     * @var string
     */
    private string $language = 'window.navigator.language';

    private ?string $ruleMessage = null;

    private WidgetOptions $widgetOptions;

    /**
     * @var array<string, mixed>
     */
    private array $options = [];

    public function __construct(
        private readonly ClientInterface $httpClient,
        private readonly RequestFactoryInterface $requestFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly ?Closure $getUserIpCallback = null
    ) {
        $this->widgetOptions = new WidgetOptions();
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
        $element->addClass('smart-captcha')
            ->removeAttribute('name')
            ->setAttribute(AttributeFactory::create('id', 'captcha-container'));

        $options = json_encode(
            array_filter(
                array_merge($this->widgetOptions->toArray(), [
                    'sitekey' => $this->publicKey,
                    'callback' => $this->widgetOptions->isInvisible() ? 'invisibleCallbackProcess' : $this->widgetOptions->callback,
                ])
            )
        );

        $formId = $element->getForm()?->getId();

        if ($formId === null) {
            throw new Exception('The Form Id cannot be null');
        }

        return <<<HTMLJS
<script src="https://smartcaptcha.yandexcloud.net/captcha.js?render=onload&onload=onloadFunction" defer></script>

<div {$element->getAttributesString()}></div>
<script>
    const invisible = {$this->widgetOptions->isInvisibleAsString()};
    const  form = document.getElementById('{$formId}');
    function onloadFunction() {
        if (window.smartCaptcha) {
            const container = document.getElementById('{$element->getAttribute('id')->getValueString()}');
            const widgetId = window.smartCaptcha.render(container, {$options});
            
            if (invisible) {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    window.smartCaptcha.execute(widgetId);
                })    
            }
        
        }
    }
    
    if (invisible) {
        function invisibleCallbackProcess(token) {
            if (typeof form.submit === 'function') {
                form.submit(); 
                console.debug('Submit with form.submit()')
            } else {
                HTMLFormElement.prototype.submit.call(form);
                console.debug('Submit with HTMLFormElement.prototype.submit.call()')
            }
        }  
    }
</script>
HTMLJS;
    }

    public function validate(Ruleable $element): bool
    {
        $token = $this->getRequest()->getParsedBody()['smart-token']
            ?? $this->getRequest()->getQueryParams()['smart-token']
            ?? null;

        if (empty($token)) {
            $element->setRuleError('Smart token is missing');
            return false;
        }

        $data = [
            'secret' => $this->privateKey,
            'token' => $token
        ];

        if ($this->getUserIpCallback !== null) {
            $data['ip'] = ($this->getUserIpCallback)();
        }

        $request = $this->requestFactory
            ->createRequest('POST', YandexSmartCaptcha::VERIFY_URL)
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withBody(
                $this->streamFactory->createStream(
                    http_build_query($data, '', '&')
                )
            );

        try {
            $response = $this->httpClient->sendRequest($request);
            $responseBody = json_decode($response->getBody()->getContents(), false, 512, JSON_THROW_ON_ERROR);

            if ($responseBody->status !== 'ok') {
                $element->setRuleError($responseBody->message);
                return false;
            }

            return true;
        } catch (JsonException $e) {
            $element->setRuleError(sprintf("Invalid response from captcha service: %s", $e->getMessage()));
            return false;
        } catch (Exception $e) {
            $element->setRuleError(sprintf("Captcha verification failed: %s", $e->getMessage()));
            return false;
        } catch (ClientExceptionInterface $e) {
            $element->setRuleError(sprintf("Network error during captcha verification: %s", $e->getMessage()));
            return false;
        }
    }

    public function setPrivateKey(#[\SensitiveParameter] string $privateKey): YandexSmartCaptcha
    {
        $this->privateKey = $privateKey;
        return $this;
    }

    public function setPublicKey(string $publicKey): YandexSmartCaptcha
    {
        $this->publicKey = $publicKey;
        return $this;
    }

    public function setWidgetOptions(WidgetOptions $widgetOptions): YandexSmartCaptcha
    {
        $this->widgetOptions = $widgetOptions;
        return $this;
    }

}
