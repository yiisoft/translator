<?php

declare(strict_types=1);

namespace Yiisoft\Translator\Tests;

use PHPUnit\Framework\TestCase;
use Yiisoft\Translator\TranslationExtractor;

final class TranslationExtractorTest extends TestCase
{
    private $incorrectDataCount = 13;
    private $correctData = [
        'defaultCategory' => [
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

    public function testSettersExtractor(): void
    {
        $extractor = new TranslationExtractor();
        $this->assertEquals('', $extractor->getDefaultCategory());

        $defaultCategoryName = 'defaultCategory';
        $extractor->setDefaultCategory($defaultCategoryName);
        $this->assertEquals($defaultCategoryName, $extractor->getDefaultCategory());
    }

    public function testDirectoryExists(): void
    {
        $notExistsPath = __DIR__ . DIRECTORY_SEPARATOR . 'not_exists_path';

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Directory "' . $notExistsPath . '" does not exist.');

        $extractor = new TranslationExtractor();
        $extractor->extract($notExistsPath);
    }

    /**
     * @group extractor
     */
    public function testExtractorWithOnlyCorrectData(): void
    {
        $path = __DIR__ . DIRECTORY_SEPARATOR . 'extractorExamples' . DIRECTORY_SEPARATOR . 'synthetic' . DIRECTORY_SEPARATOR . 'correctSamples';

        $extractor = new TranslationExtractor();
        $extractor->setDefaultCategory('defaultCategory');

        $messages = $extractor->extract($path);

        $this->assertEquals($this->correctData, $messages);
        $this->assertFalse($extractor->hasSkippedLines());
    }

    /**
     * @group extractor
     */
    public function testExtractorWithOnlyIncorrectData(): void
    {
        $path = __DIR__ . DIRECTORY_SEPARATOR . 'extractorExamples' . DIRECTORY_SEPARATOR . 'synthetic' . DIRECTORY_SEPARATOR . 'incorrectSamples';

        $extractor = new TranslationExtractor();

        $messages = $extractor->extract($path);

        $this->assertEquals([], $messages);
        $this->assertTrue($extractor->hasSkippedLines());
        $this->assertCount($this->incorrectDataCount, $extractor->getSkippedLines()[$path . DIRECTORY_SEPARATOR . 'test.php']);
    }

    /**
     * @group extractor
     */
    public function testExtractorWithOnlyBrokenData(): void
    {
        $path = __DIR__ . DIRECTORY_SEPARATOR . 'extractorExamples' . DIRECTORY_SEPARATOR . 'synthetic' . DIRECTORY_SEPARATOR . 'brokenSamples';

        $extractor = new TranslationExtractor();

        $messages = $extractor->extract($path);

        $this->assertEquals(['' => ['messageId1']], $messages);
        $this->assertTrue($extractor->hasSkippedLines());
        $this->assertCount(1, $extractor->getSkippedLines()[$path . DIRECTORY_SEPARATOR . 'test.php']);
    }

    /**
     * @group extractor
     */
    public function testExtractorWithMixedData(): void
    {
        $path = __DIR__ . DIRECTORY_SEPARATOR . 'extractorExamples' . DIRECTORY_SEPARATOR . 'synthetic';

        $extractor = new TranslationExtractor();
        $extractor->setDefaultCategory('defaultCategory');

        $messages = $extractor->extract($path, ['except' => '**/brokenSamples/*']);

        $this->assertEquals($this->correctData, $messages);
        $this->assertTrue($extractor->hasSkippedLines());
        $this->assertCount($this->incorrectDataCount, $extractor->getSkippedLines()[$path . DIRECTORY_SEPARATOR . 'incorrectSamples' . DIRECTORY_SEPARATOR . 'test.php']);
    }

    /**
     * @group extractor
     */
    public function testExtractorWithMixedDataExcludeCorrect(): void
    {
        $path = __DIR__ . DIRECTORY_SEPARATOR . 'extractorExamples' . DIRECTORY_SEPARATOR . 'synthetic';

        $extractor = new TranslationExtractor();
        $extractor->setDefaultCategory('defaultCategory');

        $messages = $extractor->extract($path, ['only' => '**.php', 'except' => ['**/correctSamples/*', '**/brokenSamples/*']]);

        $this->assertEquals([], $messages);
        $this->assertTrue($extractor->hasSkippedLines());
        $this->assertCount($this->incorrectDataCount, $extractor->getSkippedLines()[$path . DIRECTORY_SEPARATOR . 'incorrectSamples' . DIRECTORY_SEPARATOR . 'test.php']);
    }

    /**
     * @group extractor
     */
    public function testExtractorWithRealDataFromExtensionUser(): void
    {
        $path = __DIR__ . DIRECTORY_SEPARATOR . 'extractorExamples' . DIRECTORY_SEPARATOR . 'user-main';

        $extractor = new TranslationExtractor();

        $messages = $extractor->extract($path);

        $this->assertCount(75, $messages['user']);
        $this->assertFalse($extractor->hasSkippedLines());
    }
}
