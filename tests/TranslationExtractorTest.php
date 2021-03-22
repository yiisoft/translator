<?php

declare(strict_types=1);

namespace Yiisoft\Translator\Tests;

use PHPUnit\Framework\TestCase;
use Yiisoft\Translator\Extractor\TranslationExtractor;

/**
 * @group extractor
 */
final class TranslationExtractorTest extends TestCase
{
    private $incorrectDataCount = 15;
    private $correctData = [
        'defaultCategory' => [
            '',
            '',
            'messageId1',
            'messageId2',
            'messageId3',
            'messageId4',
            'messageId5',
            'messageId6',
            'messageId7',
            'messageId8',
            'messageId9',
            'messageId10',
            'messageId11',
            'messageId12',
            'messageId1.3',
            'messageId1.4',
            'messageId15',
            'messageId16',
            'messageId17',
            'messageId18',
            'messageId19',
        ],
        'categoryName' => [
            'messageId1',
            'messageId2',
            'messageId3',
            'messageId4',
            'messageId5',
            'messageId6',
            'messageId7',
            'messageId8',
            'messageId9',
            'messageId10',
            'messageId11',
            'messageId12',
            'messageId1.3',
            'messageId1.4',
            'messageId15',
            'messageId16',
            'messageId17',
        ],
        'categoryName2' => [
            'messageId1',
            'messageId2',
            'messageId3',
            'messageId4',
            'messageId5',
            'messageId6',
            'messageId7',
            'messageId8',
            'messageId9',
            'messageId10',
            'messageId11',
            'messageId12',
            'messageId1.3',
            'messageId1.4',
            'messageId15',
            'messageId16',
            'messageId17',
            'messageId18',
        ],
        'Категория1' => [
            'Сообщение1',
            'Сообщение2',
            'Сообщение3',
        ],
    ];

    private $correctDataStatic = [
        'defaultCategory' => [
            'messageId1',
            'messageId2',
            'messageId3',
            'messageId4',
        ],
    ];

    public function testDirectoryExists(): void
    {
        $notExistsPath = __DIR__ . DIRECTORY_SEPARATOR . 'not_exists_path';

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Directory "' . $notExistsPath . '" does not exist.');

        $extractor = new TranslationExtractor($notExistsPath);
        $extractor->extract();
    }

//    public function testWithTranslatorAndCorrectData(): void
//    {
//        $path = __DIR__ . DIRECTORY_SEPARATOR . 'extractorExamples' . DIRECTORY_SEPARATOR . 'synthetic' . DIRECTORY_SEPARATOR . 'correctSamples';
//
//        $extractor = new TranslationExtractor();
//        $extractorNew = $extractor->withTranslator('$translator::translate');
//        $extractorNew->setDefaultCategory('defaultCategory');
//
//        $this->assertNotEquals($extractor->getDefaultCategory(), $extractorNew->getDefaultCategory());
//
//        $messages = $extractorNew->extract($path);
//
//        $this->assertEquals($this->correctDataStatic, $messages);
//        $this->assertFalse($extractorNew->hasSkippedLines());
//    }
//
//    public function testWithEmptyTranslatorAndCorrectData(): void
//    {
//        $path = __DIR__ . DIRECTORY_SEPARATOR . 'extractorExamples' . DIRECTORY_SEPARATOR . 'synthetic' . DIRECTORY_SEPARATOR . 'correctSamples';
//
//        $this->expectException(\RuntimeException::class);
//        $this->expectExceptionMessage('Translator tokens cannot be shorttest 2 tokens.');
//
//        $extractor = (new TranslationExtractor())->withTranslator('->');
//        $extractor->setDefaultCategory('defaultCategory');
//
//        $extractor->extract($path);
//    }
//

    public function testExtractorWithOnlyCorrectData(): void
    {
        $path = __DIR__ . DIRECTORY_SEPARATOR . 'extractorExamples' . DIRECTORY_SEPARATOR . 'synthetic' . DIRECTORY_SEPARATOR . 'correctSamples';

        $extractor = new TranslationExtractor($path);
        $messages = $extractor->extract('defaultCategory');

        $this->assertEquals($this->correctData, $messages);
        $this->assertFalse($extractor->hasSkippedLines());
    }

    public function testExtractorWithOnlyIncorrectData(): void
    {
        $path = __DIR__ . DIRECTORY_SEPARATOR . 'extractorExamples' . DIRECTORY_SEPARATOR . 'synthetic' . DIRECTORY_SEPARATOR . 'incorrectSamples';

        $extractor = new TranslationExtractor($path);

        $messages = $extractor->extract();

        $this->assertEquals([], $messages);
        $this->assertTrue($extractor->hasSkippedLines());
        $this->assertCount($this->incorrectDataCount, current($extractor->getSkippedLines()));
    }

    public function testExtractorWithOnlyBrokenData(): void
    {
        $path = __DIR__ . DIRECTORY_SEPARATOR . 'extractorExamples' . DIRECTORY_SEPARATOR . 'synthetic' . DIRECTORY_SEPARATOR . 'brokenSamples';

        $extractor = new TranslationExtractor($path);

        $messages = $extractor->extract();

        $this->assertEquals(['' => ['messageId1']], $messages);
        $this->assertTrue($extractor->hasSkippedLines());
        $this->assertCount(1, current($extractor->getSkippedLines()));
    }

    public function testExtractorWithMixedData(): void
    {
        $path = __DIR__ . DIRECTORY_SEPARATOR . 'extractorExamples' . DIRECTORY_SEPARATOR . 'synthetic';

        $extractor = new TranslationExtractor($path, null, ['**/brokenSamples/*']);

        $messages = $extractor->extract('defaultCategory');

        $this->assertEquals($this->correctData, $messages);
        $this->assertTrue($extractor->hasSkippedLines());
        $this->assertCount($this->incorrectDataCount, current($extractor->getSkippedLines()));
    }

    public function testExtractorWithMixedDataExcludeCorrect(): void
    {
        $path = __DIR__ . DIRECTORY_SEPARATOR . 'extractorExamples' . DIRECTORY_SEPARATOR . 'synthetic';

        $extractor = new TranslationExtractor($path, ['**.php'], ['**/correctSamples/*', '**/brokenSamples/*']);

        $messages = $extractor->extract('defaultCategory');

        $this->assertEquals([], $messages);
        $this->assertTrue($extractor->hasSkippedLines());
        $this->assertCount($this->incorrectDataCount, current($extractor->getSkippedLines()));
    }

    /**
     * @group extractor
     */
    public function testExtractorWithRealDataFromExtensionUser(): void
    {
        $path = __DIR__ . DIRECTORY_SEPARATOR . 'extractorExamples' . DIRECTORY_SEPARATOR . 'user-main';

        $extractor = new TranslationExtractor($path, ['**.php']);

        $messages = $extractor->extract();

        $this->assertCount(75, $messages['user']);
        $this->assertFalse($extractor->hasSkippedLines());
    }
}
