<?php

declare(strict_types=1);

namespace Yiisoft\Translator\Tests;

use PHPUnit\Framework\TestCase;
use Yiisoft\Translator\Extractor\ContentParser;

/**
 * @group extractor
 */
final class ContentParserTest extends TestCase
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

    public function testSettersExtractor(): void
    {
        $parser = new ContentParser();
        $this->assertEquals('', $parser->getDefaultCategory());

        $oldDefaultCategoryName = 'oldDefaultCategory';
        $parser = new ContentParser($oldDefaultCategoryName);
        $this->assertEquals($oldDefaultCategoryName, $parser->getDefaultCategory());

        $defaultCategoryName = 'defaultCategory';
        $parser->setDefaultCategory($defaultCategoryName);
        $this->assertEquals($defaultCategoryName, $parser->getDefaultCategory());
    }

    public function testWithTranslatorAndCorrectData(): void
    {
        $fileName = __DIR__ . '/extractorExamples/synthetic/correctSamples/test-static.php';
        $fileContent = file_get_contents($fileName);

        $parser = new ContentParser('defaultCategory', '$translator::translate');

        $messages = $parser->extract($fileContent);

        $this->assertEquals($this->correctDataStatic, $messages);
        $this->assertFalse($parser->hasSkippedLines());
        $this->assertEquals([], $parser->getSkippedLines());
    }

    public function testWithEmptyTranslatorAndCorrectData(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Translator tokens cannot be shorttest 2 tokens.');

        $extractor = new ContentParser(null, '->');
    }

    public function testExtractorWithOnlyCorrectData(): void
    {
        $fileName = __DIR__ . '/extractorExamples/synthetic/correctSamples/test.php';
        $fileContent = file_get_contents($fileName);

        $extractor = new ContentParser();
        $extractor->setDefaultCategory('defaultCategory');

        $messages = $extractor->extract($fileContent);

        $this->assertEquals($this->correctData, $messages);
        $this->assertFalse($extractor->hasSkippedLines());
    }

    public function testExtractorWithOnlyIncorrectData(): void
    {
        $fileName = __DIR__ . '/extractorExamples/synthetic/incorrectSamples/test.php';
        $fileContent = file_get_contents($fileName);

        $extractor = new ContentParser();

        $messages = $extractor->extract($fileContent);

        $this->assertEquals([], $messages);
        $this->assertTrue($extractor->hasSkippedLines());
        $this->assertCount($this->incorrectDataCount, $extractor->getSkippedLines());
    }

    public function testExtractorWithOnlyBrokenData(): void
    {
        $fileName = __DIR__ . '/extractorExamples/synthetic/brokenSamples/test.php';
        $fileContent = file_get_contents($fileName);

        $extractor = new ContentParser();

        $messages = $extractor->extract($fileContent);

        $this->assertEquals(['' => ['messageId1']], $messages);
        $this->assertTrue($extractor->hasSkippedLines());
        $this->assertCount(1, $extractor->getSkippedLines());
    }

//    /**
//     * @group extractor
//     */
//    public function testExtractorWithRealDataFromExtensionUser(): void
//    {
//        $path = __DIR__ . DIRECTORY_SEPARATOR . 'extractorExamples' . DIRECTORY_SEPARATOR . 'user-main';
//
//        $extractor = new TranslationExtractor();
//
//        $messages = $extractor->extract($path, ['**.php']);
//
//        $this->assertCount(75, $messages['user']);
//        $this->assertFalse($extractor->hasSkippedLines());
//    }
}
