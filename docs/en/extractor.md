## Translation Extractor

Extractor can get messages to translate from a set of PHP files. Usage is the following:

```php
$path = '/path/to/your/project';

$extractor = new \Yiisoft\Translator\Extractor\TranslationExtractor($path);

$defaultCategory = 'defaultCategoryName';
$translatorCall = '->translate';

$messages = $extractor->extract($defaultCategory, $translatorCall);
// Result is same as from `extract` function of ContentParser (see below).
```

### Adding files with non-`.php` extension or skipping some directories

```php
$path = '/path/to/your/project';
$only = ['**.php', '**.php7'];
$except = ['**/brokenSamples/*'];
$extractor = new \Yiisoft\Translator\Extractor\TranslationExtractor($path, $only, $except);
```

For more information about `only` and `except` parameters [see yiisoft/files](https://github.com/yiisoft/files).

### Getting a list of issues while extracting messages

In case you have complicated parameters, such as callbacks, constants etc., extractor may skip some lines:

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

## Parsing content directly

Extractor uses `ContentParser` internally which is applied to each file. You may want to apply it to a single file
as well:

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

`$messages` will contain the following array:

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
