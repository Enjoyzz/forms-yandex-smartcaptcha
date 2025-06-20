<?php

declare(strict_types=1);

namespace Tests\Enjoys\Forms\Captcha\YandexSmartCaptcha;

use Enjoys\Forms\Captcha\YandexSmartCaptcha\Language;
use Enjoys\Forms\Captcha\YandexSmartCaptcha\ShieldPosition;
use Enjoys\Forms\Captcha\YandexSmartCaptcha\WidgetOptions;
use PHPUnit\Framework\TestCase;

class WidgetOptionsTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $options = new WidgetOptions();

        $this->assertNull($options->hl);
        $this->assertNull($options->callback);
        $this->assertNull($options->invisible);
        $this->assertNull($options->shieldPosition);
        $this->assertNull($options->hideShield);
        $this->assertNull($options->webview);
        $this->assertNull($options->test);
    }

    public function testStringToEnumConversion(): void
    {
        $options = new WidgetOptions(
            hl: 'ru',
            shieldPosition: 'top-right'
        );

        $this->assertSame(Language::RU, $options->hl);
        $this->assertSame(ShieldPosition::TOP_RIGHT, $options->shieldPosition);
    }

    public function testInvalidStringToEnumConversion(): void
    {
        $options = new WidgetOptions(
            hl: 'invalid',
            shieldPosition: 'invalid'
        );

        $this->assertNull($options->hl);
        $this->assertNull($options->shieldPosition);
    }

    public function testDirectEnumValues(): void
    {
        $options = new WidgetOptions(
            hl: Language::EN,
            shieldPosition: ShieldPosition::BOTTOM_LEFT
        );

        $this->assertSame(Language::EN, $options->hl);
        $this->assertSame(ShieldPosition::BOTTOM_LEFT, $options->shieldPosition);
    }

    public function testToArrayMethod(): void
    {
        $options = new WidgetOptions(
            hl: 'en',
            callback: 'myCallback',
            invisible: true,
            shieldPosition: 'bottom-right',
            hideShield: true,
            webview: false,
            test: true
        );

        $expected = [
            'hl' => 'en',
            'shieldPosition' => 'bottom-right',
            'hideShield' => true,
            'invisible' => true,
            'webview' => false,
            'test' => true,
            'callback' => 'myCallback',
        ];

        $this->assertSame($expected, $options->toArray());
    }

    public function testToArrayWithNullValues(): void
    {
        $options = new WidgetOptions();

        $expected = [
            'hl' => null,
            'shieldPosition' => null,
            'hideShield' => null,
            'invisible' => null,
            'webview' => null,
            'test' => null,
            'callback' => null,
        ];

        $this->assertSame($expected, $options->toArray());
    }

    public function testIsInvisibleMethod(): void
    {
        $options1 = new WidgetOptions(invisible: true);
        $options2 = new WidgetOptions(invisible: false);
        $options3 = new WidgetOptions();

        $this->assertTrue($options1->isInvisible());
        $this->assertFalse($options2->isInvisible());
        $this->assertFalse($options3->isInvisible());
    }

    public function testIsInvisibleAsStringMethod(): void
    {
        $options1 = new WidgetOptions(invisible: true);
        $options2 = new WidgetOptions(invisible: false);
        $options3 = new WidgetOptions();

        $this->assertSame('true', $options1->isInvisibleAsString());
        $this->assertSame('false', $options2->isInvisibleAsString());
        $this->assertSame('false', $options3->isInvisibleAsString());
    }

    public function testAllProperties(): void
    {
        $options = new WidgetOptions(
            hl: 'tr',
            callback: 'customCallback',
            invisible: true,
            shieldPosition: 'center-left',
            hideShield: true,
            webview: true,
            test: true
        );

        $this->assertSame(Language::TR, $options->hl);
        $this->assertSame('customCallback', $options->callback);
        $this->assertTrue($options->invisible);
        $this->assertSame(ShieldPosition::CENTER_LEFT, $options->shieldPosition);
        $this->assertTrue($options->hideShield);
        $this->assertTrue($options->webview);
        $this->assertTrue($options->test);
    }
}