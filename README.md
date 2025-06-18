# forms-recaptcha
addon for enjoys/forms

## Run built-in server for view example
```shell
php -S localhost:8000 -t ./example .route
```

### Usage

```php 
// ...before code
// Optional. Set ID for form (for V2Invisible and V3) 
$form->setAttribute(AttributeFactory::create('id', uniqid()));
// Init captcha
$captcha = new reCaptcha($Psr18_HttpClient, $Psr7RequestFactory, $Psr7StreamFactory);

$captcha->setOptions([
    'type' => V3::class, //V2Invisible, V2, V3
    'publicKey' => '...',
    'privateKey' => '...',
    'submitEl' => 'submit1',
    // more options ...
]);

$form->captcha($captcha);
$form->submit('submit1');
// more code...
```
