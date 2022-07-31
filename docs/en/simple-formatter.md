## Simple Formatter

## Configuration

In case you use [`yiisoft/config`](http://github.com/yiisoft/config), you will get configuration automatically. If not, the following DI container configuration is necessary:

```php
<?php

declare(strict_types=1);

use Yiisoft\Translator\MessageFormatterInterface;
use Yiisoft\Translator\SimpleMessageFormatter;

return [
    MessageFormatterInterface::class => SimpleMessageFormatter::class,
];
```

### Example of usage

```php
/** @var \Yiisoft\Translator\Translator $translator **/

$categoryName = 'moduleId';
$pathToModuleTranslations = './module/messages/';
$additionalCategory = new Yiisoft\Translator\CategorySource(
    $categoryName, 
    new \Yiisoft\Translator\Message\Php\MessageSource($pathToModuleTranslations),
    new \Yiisoft\Translator\SimpleMessageFormatter()
);
$translator->addCategorySource($additionalCategory);

$translator->translate('Test string: {str}', ['str' => 'string data'], 'moduleId', 'en');
// output: Test string: string data
```

### Example of usage without `yiisoft/translator` package

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
