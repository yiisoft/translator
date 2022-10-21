<?php

declare(strict_types=1);

namespace Yiisoft\Translator\Tests;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Yiisoft\Translator\CategorySource;
use Yiisoft\Translator\MessageFormatterInterface;
use Yiisoft\Translator\MessageReaderInterface;
use Yiisoft\Translator\NullMessageFormatter;
use Yiisoft\Translator\SimpleMessageFormatter;

final class CategoryTest extends TestCase
{
    public function testName(): void
    {
        $this->assertInstanceOf(CategorySource::class, new CategorySource(
            'testcategoryname',
            $this->createMessageReader(),
            $this->createMessageFormatter()
        ));
    }

    public function testNameException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Category name is invalid. Only letters and numbers are allowed.');
        new CategorySource(
            'test category name',
            $this->createMessageReader(),
            $this->createMessageFormatter()
        );
    }

    public function dataWithoutFormatter(): array
    {
        return [
            'null formatter' => [
                'test message {n}',
                'test message {n}',
                ['n' => 7],
                new NullMessageFormatter(),
            ],
            'simple formatter' => [
                'test message 7',
                'test message {n}',
                ['n' => 7],
                new SimpleMessageFormatter(),
            ],
        ];
    }

    /**
     * @dataProvider dataWithoutFormatter
     */
    public function testWithoutFormatter(
        string $expectedMessage,
        string $message,
        array $parameters,
        MessageFormatterInterface $defaultMessageFormatter
    ): void {
        $categorySource = new CategorySource('test', $this->createMessageReader());
        $this->assertSame(
            $expectedMessage,
            $categorySource->format($message, $parameters, 'en_US', $defaultMessageFormatter)
        );
    }

    private function createMessageReader(): MessageReaderInterface
    {
        return new class () implements MessageReaderInterface {
            public function getMessage(string $id, string $category, string $locale, array $parameters = []): ?string
            {
                return null;
            }

            public function getMessages(string $category, string $locale): array
            {
                return [];
            }
        };
    }

    private function createMessageFormatter(): MessageFormatterInterface
    {
        return new class () implements MessageFormatterInterface {
            public function format(string $message, array $parameters, string $locale): string
            {
                return $message;
            }
        };
    }
}
