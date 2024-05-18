# Formatador `intl`

O formatador de mensagens `intl` utiliza recursos de formatação de mensagens de extensão intl do PHP.

## Requisitos

- `intl` Extensão PHP 1.0.2 ou superior.
- Biblioteca `ICU` 49.0 ou superior.

## Configuração

Caso use [`yiisoft/config`](https://github.com/yiisoft/config), você obterá a configuração automaticamente. Se não,
a seguinte configuração do contêiner DI é necessária:

```php
use Yiisoft\Translator\MessageFormatterInterface;
use Yiisoft\Translator\IntlMessageFormatter;

return [
    MessageFormatterInterface::class => IntlMessageFormatter::class,
];
```

## Uso geral

### Exemplo de uso com `yiisoft/translator`

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

### Exemplo de uso sem o pacote `yiisoft/translator`

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

Para obter uma lista de opções disponíveis para a localidade que você está usando consulte
[https://intl.rmcreative.ru/](https://intl.rmcreative.ru/)
