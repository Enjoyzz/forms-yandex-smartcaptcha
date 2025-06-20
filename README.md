# Yandex SmartCaptcha

addon for enjoys/forms

## Run built-in server for view example

```shell
php -S localhost:8000 -t ./example .route
```

### Usage

```php
// ...before code

// Required. Set ID for form.
$form->setAttribute(AttributeFactory::create('id', uniqid()));
// or
new Form(id: uniqid())
// or
$form->setId(uniqid())

// Init YandexSmartCaptcha
$captcha = new YandexSmartCaptcha($Psr18_HttpClient, $Psr7RequestFactory, $Psr7StreamFactory);
$captcha
    //
    ->setPublicKey('...')
    ->setPrivateKey('...')
    ->setWidgetOptions(
        // Optional. Full options
        new WidgetOptions(
            hl: Language::RU, // Language enum or as string 'en' or null (default - null)
            callback: 'myCallback', // string or null (default - null)
            invisible: false, // bool or null (default - null)
            shieldPosition: ShieldPosition::CENTER_LEFT, // only with invisible. ShieldPosition enum or string or null  (default - null)
            hideShield: false,  // only with invisible. bool or null (default - null)
            webview: false, //bool or null (default - null)
            test: true // bool or null (default - null)
        )
    );
```
