<?php

declare(strict_types=1);


namespace Enjoys\Forms\Captcha\YandexSmartCaptcha;


final class WidgetOptions
{

    public function __construct(
        /**
         * Язык виджета.
         * 'ru' | 'en' | 'be' | 'kk' | 'tt' | 'uk' | 'uz' | 'tr'
         * @var string|null
         */
        private ?string $hl = null,

        /**
         * Невидимая капча.
         * Вы обязаны уведомлять пользователей о том, что их данные обрабатывает SmartCaptcha.
         * Если вы скрываете блок с уведомлением, сообщите пользователям иным способом о том,
         * что SmartCaptcha обрабатывает их данные.
         * @var bool|null
         * @see https://yandex.cloud/ru/docs/smartcaptcha/concepts/invisible-captcha
         */
        private ?bool $invisible = null,

        /**
         * Расположение блока с уведомлением об обработке данных. (Для невидимой капчи)
         * `top-left` | `center-left` | `bottom-left` | `top-right` | `center-right` | `bottom-right`
         * @var string|null
         */
        private ?string $shieldPosition = null,

        /**
         * Скрыть блок с уведомлением об обработке данных. (Для невидимой капчи)
         * Вы обязаны уведомлять пользователей о том, что их данные обрабатывает SmartCaptcha.
         * Если вы скрываете блок с уведомлением, сообщите пользователям иным способом о том,
         * что SmartCaptcha обрабатывает их данные.
         * @var bool|null
         */
        private ?bool $hideShield = null,

        /**
         * Запуск капчи в WebView. Используется для повышения точности оценки пользователей при
         * добавлении капчи в мобильные приложения с помощью WebView.
         * @var bool|null
         */
        private ?bool $webview = null,

        /**
         * Включение работы капчи в режиме тестирования. Пользователь всегда будет получать задание.
         * Используйте это свойство только для отладки и тестирования.
         * @var bool|null
         */
        private ?bool $test = null,

        /**
         * Функция-обработчик.
         * (token: string) => void
         * @var string|null
         */
        private ?string $callback = null,
    ) {
    }

    public function toArray(): array
    {
        return array_filter([
            'hl' => $this->hl,
            'shieldPosition' => $this->shieldPosition,
            'hideShield' => $this->hideShield,
            'invisible' => $this->invisible,
            'webview' => $this->webview,
            'test' => $this->test,
            'callback' => $this->callback,
        ]);
    }

    public function isInvisible(): bool
    {
        return $this->invisible ?? false;
    }
}