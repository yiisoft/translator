# Пакет Translator

Translator — пакет спроектированный для перевода сообщений.

Пакет позволяет реализовать интернационализцаию как в приложениях на базе Yii, так и самостоятельных PHP приложениях.

# Установка

Предпочтительнее установить этот пакет через [Composer](https://getcomposer.org/download/):

```bash
composer require yiisoft/translator
```

## Настройка

## Инициализация
```php
// Получение eventDispatcher из DI
$eventDispatcher = $DIContainer->get(\Psr\EventDispatcher\EventDispatcherInterface::class);

$defaultCategoryName = 'app';
$locale = 'ru';

$pathToTranslations = './messages/';
// Источник сообщений на PHP файлах
$messageSource = new \Yiisoft\Translator\Message\Php\MessageSource($pathToTranslations);
// Форматировщик сообщений с использованием i18n-форматтера
$formatter = new \Yiisoft\Translator\Formatter\Intl\IntlMessageFormatter(); 

$category = new Yiisoft\Translator\Category(
    $defaultCategoryName, 
    $messageSource,
    $formatter
);

$translator = new Yiisoft\Translator\Translator(
    $category,
    $locale,
    $eventDispatcher
);

/**
 * При использовании нескольких источников переводов
 */
$categoryName = 'module';
$pathToModuleTranslations = './module/messages/';
$moduleMessageSource = new \Yiisoft\Translator\Message\Php\MessageSource($pathToModuleTranslations);

// Простой форматтер сообщений
$formatter = new \Yiisoft\Translator\Formatter\Simple\SimpleMessageFormatter();

$additionalCategory = new Yiisoft\Translator\Category(
    $categoryName, 
    $moduleMessageSource,
    $formatter
);
$translator->addCategorySource($additionalCategory);
```

## Использование
```php

// Использование языка и категории по-умолчанию
$messageIdentificator = 'submit';

echo $translator->translate($messageIdentificator);
// output: `Submit message`

$messageIdentificator = 'multiHumans';
echo $translator->translate($messageIdentificator, ['n' => 3]);
// output: `3 humans`

// Использование перевода из указанной категории, с указанием языка
$messageIdentificator = 'submit';
echo $translator->translate($messageIdentificator, [], 'moduleId', 'ru');
// output: `Отправить сообщение`
```
