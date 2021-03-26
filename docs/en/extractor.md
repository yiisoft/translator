## Translation Extractor

Using for extracts translate messages from many php files

```php
$path = '/path/to/your/project';

$extractor = new \Yiisoft\Translator\Extractor\TranslationExtractor($path);

$defaultCategory = 'defaultCategoryName';
$translatorCall = '->translate';

$messages = $extractor->extract($defaultCategory, $translatorCall);
// Result is same as from `extract` function of ContentParser
```

### Adding files with extension not `.php` or skipping some directories
```php
$path = '/path/to/your/project';
$only = ['**.php', '**.php7'];
$except = ['**/brokenSamples/*'];
$extractor = new \Yiisoft\Translator\Extractor\TranslationExtractor($path, $only, $except);
```

For more info about parameters `only` and `except` see in package [yiisoft/files](https://github.com/yiisoft/files)

### Getting skipped lines from your project (with some complexity parameters - with callback, constants etc.)

```php
/** @var \Yiisoft\Translator\Extractor\TranslationExtractor $extractor */
$messages = $extractor->extract();

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

## Direct content parse

Using for extracts translate messages from single php file

```php
/**
 * Default category for messages without setted category in translator call. 
 * Example $translator->translate('SimpleText');
 * Optional. By default this value equal empty string
 */
$defaultCategory = 'defaultCategoryName';
/**
 * 
 * Translator function call signature
 * Optional. By default using default call signature `->translate`
 */  
$translatorCall = '::translate';

$parser = new \Yiisoft\Translator\Extractor\ContentParser($defaultCategory, $translatorCall);

$fileContent = file_get_contents('some_file.php');
$messages = $parser->extract($fileContent);
/**
 Into variable $messages will be returned array:
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
 */
```
