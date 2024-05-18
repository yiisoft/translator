# Extrator de tradução

O extrator pode fazer com que mensagens sejam traduzidas de um conjunto de arquivos PHP.

## Requisitos

- Extensão PHP `tokenizer`.

## Uso geral

O uso é o seguinte:

```php
$path = '/path/to/your/project';

$extractor = new \Yiisoft\Translator\Extractor\TranslationExtractor($path);

$defaultCategory = 'defaultCategoryName';
$translatorCall = '->translate';

$messages = $extractor->extract($defaultCategory, $translatorCall);
// Result is same as from `extract` function of ContentParser (see below).
```

## Adicionando arquivos com extensão diferente de `.php` ou ignorando alguns diretórios

```php
$path = '/path/to/your/project';
$only = ['**.php', '**.php7'];
$except = ['**/brokenSamples/*'];
$extractor = new \Yiisoft\Translator\Extractor\TranslationExtractor($path, $only, $except);
```

Para obter mais informações sobre os parâmetros `only` e `except` [veja yiisoft/files](https://github.com/yiisoft/files).

## Obtendo uma lista de problemas ao extrair mensagens

Caso você tenha parâmetros complicados, como retornos de chamada, constantes etc, o extrator pode pular algumas linhas:

```php
/** @var \Yiisoft\Translator\Extractor\TranslationExtractor $extractor */
$defaultCategory = 'defaultCategoryName';
$messages = $extractor->extract($defaultCategory);

if ($extractor->hasSkippedLines()) {
    $skippedLines = $extractor->getSkippedLines();
    /**
     * Will be returned array looks like: 
     * [
     *     '/path/to/fileName' => [
     *         [
     *             int $numberOfLine,
     *             string $incorrectLine,
     *         ],
     * ];
     */
}
```

## Analisando conteúdo diretamente

O extrator usa `ContentParser` internamente, que é aplicado a cada arquivo. Você pode querer aplicá-lo a um único arquivo
também:

```php
/**
 * Default category for messages without translator call category set. 
 * For example, $translator->translate('SimpleText');
 * Optional. By default this value equals empty string.
 */
$defaultCategory = 'defaultCategoryName';
/**
 * 
 * Translator method call signature.
 * Optional. By default using default call signature `->translate`.
 */  
$translatorCall = '::translate';

$parser = new \Yiisoft\Translator\Extractor\ContentParser($defaultCategory, $translatorCall);

$fileContent = file_get_contents('some_file.php');
$messages = $parser->extract($fileContent);
```

`$messages` conterá o seguinte array:

```php
[
    'defaultCategoryName' => [
        'messageId1',
        'messageId2',
    ],
    'categoryName' => [
        'messageId3',
        'messageId4',
    ],
]
```
