<?php

declare(strict_types=1);


namespace Enjoys\Forms\Captcha\YandexSmartCaptcha;


final class WidgetOptions
{

    /**
     * Расположение блока с уведомлением об обработке данных. (Для невидимой капчи)
     * @var ShieldPosition|null
     */
    public readonly ?ShieldPosition $shieldPosition;

    /**
     *  Язык виджета.
     * @var Language|null
     */
    public readonly ?Language $hl;

    public function __construct(
        /**
         * Язык виджета.
         * 'ru' | 'en' | 'be' | 'kk' | 'tt' | 'uk' | 'uz' | 'tr'
         * @var Language|string|null $hl
         */
        Language|string|null $hl = null,


        /**
         * Функция-обработчик. При невидимой капче автоматически меняет callback функцию на встроенную, для отправки формы
         * (token: string) => void
         * @var string|null $callback
         */
        public readonly ?string $callback = null,

        /**
         * Невидимая капча.
         * Вы обязаны уведомлять пользователей о том, что их данные обрабатывает SmartCaptcha.
         * Если вы скрываете блок с уведомлением, сообщите пользователям иным способом о том,
         * что SmartCaptcha обрабатывает их данные.
         * @var bool|null $invisible
         * @see https://yandex.cloud/ru/docs/smartcaptcha/concepts/invisible-captcha
         */
        public readonly ?bool $invisible = null,

        /**
         * Расположение блока с уведомлением об обработке данных. (Для невидимой капчи)
         * `top-left` | `center-left` | `bottom-left` | `top-right` | `center-right` | `bottom-right`
         * @var string|null $shieldPosition
         */
        ShieldPosition|string|null $shieldPosition = null,

        /**
         * Скрыть блок с уведомлением об обработке данных. (Для невидимой капчи)
         * Вы обязаны уведомлять пользователей о том, что их данные обрабатывает SmartCaptcha.
         * Если вы скрываете блок с уведомлением, сообщите пользователям иным способом о том,
         * что SmartCaptcha обрабатывает их данные.
         * @var bool|null $hideShield
         */
        public readonly ?bool $hideShield = null,

        /**
         * Запуск капчи в WebView. Используется для повышения точности оценки пользователей при
         * добавлении капчи в мобильные приложения с помощью WebView.
         * @var bool|null $webview
         */
        public readonly ?bool $webview = null,

        /**
         * Включение работы капчи в режиме тестирования. Пользователь всегда будет получать задание.
         * Используйте это свойство только для отладки и тестирования.
         * @var bool|null $test
         */
        public readonly ?bool $test = null,
    ) {
        $this->shieldPosition = is_string($shieldPosition) ? ShieldPosition::tryFrom($shieldPosition) : $shieldPosition;
        $this->hl = is_string($hl) ? Language::tryFrom($hl) : $hl;
    }

    public function toArray(): array
    {
        return [
            'hl' => $this->hl?->value,
            'shieldPosition' => $this->shieldPosition?->value,
            'hideShield' => $this->hideShield,
            'invisible' => $this->invisible,
            'webview' => $this->webview,
            'test' => $this->test,
            'callback' => $this->callback,
        ];
    }

    public function isInvisible(): bool
    {
        return $this->invisible ?? false;
    }

    public function isInvisibleAsString(): string
    {
        return $this->invisible ? 'true' : 'false';
    }
}