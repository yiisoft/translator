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
    private int $incorrectDataCount = 16;

    private array $correctDataStatic = [
        'defaultCategory' => [
            'messageId1',
            'messageId2',
            'messageId3',
            'messageId4',
        ],
    ];

    public function testSettersExtractor(): void
    {
        $fileName = __DIR__ . '/extractorExamples/synthetic/correctSamples/test-static.php';
        $fileContent = file_get_contents($fileName);

        $parser = new ContentParser('', '$translator::translate');
        $messages = $parser->extract($fileContent);

        $this->assertEquals([''], array_keys($messages));

        $defaultCategoryName = 'defaultCategory';
        $parser->setDefaultCategory($defaultCategoryName);
        $messages = $parser->extract($fileContent);

        $this->assertEquals(['defaultCategory'], array_keys($messages));
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

        new ContentParser(null, '->');
    }

    public function testExtractorWithOnlyCorrectData(): void
    {
        $fileName = __DIR__ . '/extractorExamples/synthetic/correctSamples/test.php';
        $fileContent = file_get_contents($fileName);

        $extractor = new ContentParser();
        $extractor->setDefaultCategory('defaultCategory');

        $messages = $extractor->extract($fileContent);

        $correctData = include 'data/correct-data.php';

        $this->assertEquals($correctData, $messages);
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
}
