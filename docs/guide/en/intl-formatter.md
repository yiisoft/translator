# `intl` formatter

`intl` message formatter utilizes PHP intl extension message formatting capabilities.

## Requirements

- `intl` PHP extension 1.0.2 or higher.
- `ICU` library 49.0 or higher.

## Configuration

In case you use [`yiisoft/config`](http://github.com/yiisoft/config), you will get configuration automatically. If not,
the following DI container configuration is necessary:

```php
use Yiisoft\Translator\MessageFormatterInterface;
use Yiisoft\Translator\IntlMessageFormatter;

return [
    MessageFormatterInterface::class => IntlMessageFormatter::class,
];
```

## General usage

### Example of usage with `yiisoft/translator`

```php
/** @var \Yiisoft\Translator\Translator $translator **/

$categoryName = 'moduleId';
$pathToModuleTranslations = './module/messages/';
$additionalCategorySource = new Yiisoft\Translator\CategorySource(
    $categoryName, 
    new \Yiisoft\Translator\Message\Php\MessageSource($pathToModuleTranslations),
    new \Yiisoft\Translator\IntlMessageFormatter()
);
$translator->addCategorySources($additionalCategorySource);

$translator->translate('Test string: {str}', ['str' => 'string data'], 'moduleId', 'en');
// output: Test string: string data
```

### Example of usage without `yiisoft/translator` package

```php
/** @var \Yiisoft\Translator\IntlMessageFormatter $formatter */
$pattern = 'Total {count, number} {count, plural, one{item} other{items}}.';
$params = ['count' => 1];
$locale = 'en';
echo $formatter->format($pattern, $params, $locale);
// output: Total 1 item. 

$pattern = '{gender, select, female{Уважаемая} other{Уважаемый}} {firstname}';
$params = ['gender' => null, 'firstname' => 'Vadim'];
echo $formatter->format($pattern, $params, 'ru');
// output: Уважаемый Vadim 

$pattern = '{name} is {gender} and {gender, select, female{she} male{he} other{it}} loves Yii!';
$params = ['name' => 'Alexander', 'gender' => 'male'];
echo $formatter->format($pattern, $params, $locale);
// output: Alexander is male and he loves Yii! 
```

To get a list of options available for locale you're using see
[https://intl.rmcreative.ru/](https://intl.rmcreative.ru/)
