# Simple Formatter

## Configuration

In case of using [yiisoft/config](http://github.com/yiisoft/config), the configuration is added automatically. If not,
add the following mapping:

```php
use Yiisoft\Translator\MessageFormatterInterface;
use Yiisoft\Translator\SimpleMessageFormatter;

return [
    MessageFormatterInterface::class => SimpleMessageFormatter::class,
];
```

## Using with `Translator`

```php
/** @var \Yiisoft\Translator\Translator $translator **/

$categoryName = 'moduleId';
$pathToModuleTranslations = './module/messages/';
$additionalCategory = new Yiisoft\Translator\CategorySource(
    $categoryName, 
    new \Yiisoft\Translator\Message\Php\MessageSource($pathToModuleTranslations),
    new \Yiisoft\Translator\SimpleMessageFormatter()
);
$translator->addCategorySources($additionalCategory);

$translator->translate('Test string: {str}', ['str' => 'string data'], 'moduleId', 'en');
// output: Test string: string data
```

## Using without `Translator`

```php
/** @var \Yiisoft\Translator\SimpleMessageFormatter $formatter */
$pattern = 'Test number: {number}';
$params = ['number' => 5];
$locale = 'en';
echo $formatter->format($pattern, $params, $locale);
// output: Test number: 5

$pattern = 'Test string: {str}';
$params = ['str' => 'string data'];
echo $formatter->format($pattern, $params, $locale);
// output: Test string: string data 
```
