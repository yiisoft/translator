<?php

declare(strict_types=1);

namespace Yiisoft\Translator\Tests;

use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Yiisoft\Translator\Category;
use Yiisoft\Translator\Event\MissingTranslationCategoryEvent;
use Yiisoft\Translator\Event\MissingTranslationEvent;
use Yiisoft\Translator\MessageFormatterInterface;
use Yiisoft\Translator\MessageReaderInterface;

final class CategoryTest extends TestCase
{
    public function testName(): void
    {
        $this->assertInstanceOf(Category::class, new Category(
            'testcategoryname',
            $this->createMessageReader(),
            $this->createMessageFormatter()
        ));
    }

    public function testNameException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Category name is invalid. Only letters and numbers are allowed.');
        new Category(
            'test category name',
            $this->createMessageReader(),
            $this->createMessageFormatter()
        );
    }

    private function createMessageReader(): MessageReaderInterface
    {
        return (new class() implements MessageReaderInterface {
            public function getMessage(string $id, string $category, string $locale, array $parameters = []): ?string
            {
                return null;
            }
        });
    }

    private function createMessageFormatter(): MessageFormatterInterface
    {
        return (new class() implements MessageFormatterInterface {
            public function format(string $message, array $parameters, string $locale): string
            {
                return $message;
            }
        });
    }
}
