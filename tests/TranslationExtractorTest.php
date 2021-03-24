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
    private array $correctData = [];
    private array $incorrectData = [];

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        $this->correctData = include 'data/correct-data.php';
        $this->incorrectData = include 'data/incorrect-data.php';
        parent::__construct($name, $data, $dataName);
    }

    public function testDirectoryExists(): void
    {
        $notExistsPath = __DIR__ . DIRECTORY_SEPARATOR . 'not_exists_path';

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Directory "' . $notExistsPath . '" does not exist.');

        $extractor = new TranslationExtractor($notExistsPath);
        $extractor->extract();
    }

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
        $this->assertEquals($this->incorrectData, current($extractor->getSkippedLines()));
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
        $this->assertEquals($this->incorrectData, current($extractor->getSkippedLines()));
    }

    public function testExtractorWithMixedDataAndCorrectExclude(): void
    {
        $path = __DIR__ . DIRECTORY_SEPARATOR . 'extractorExamples' . DIRECTORY_SEPARATOR . 'synthetic';

        $extractor = new TranslationExtractor($path, ['**.php'], ['**/correctSamples/*', '**/brokenSamples/*']);

        $messages = $extractor->extract('defaultCategory');

        $this->assertEquals([], $messages);
        $this->assertTrue($extractor->hasSkippedLines());
        $this->assertEquals($this->incorrectData, current($extractor->getSkippedLines()));
    }

    /**
     * Test exxtractor on real package: yii-extension/user
     *
     * @link https://github.com/yii-extension/user
     */
    public function testExtractorWithRealDataFromUserExtension(): void
    {
        $path = __DIR__ . DIRECTORY_SEPARATOR . 'extractorExamples' . DIRECTORY_SEPARATOR . 'user-main';

        $extractor = new TranslationExtractor($path, ['**.php']);

        $messages = $extractor->extract();

        $this->assertCount(75, $messages['user']);
        $this->assertFalse($extractor->hasSkippedLines());
    }
}
