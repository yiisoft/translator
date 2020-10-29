<?php

declare(strict_types=1);

namespace Yiisoft\Translator\Tests;

use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Yiisoft\Translator\Category;
use Yiisoft\Translator\Event\MissingTranslationCategoryEvent;
use Yiisoft\Translator\Event\MissingTranslationEvent;
use Yiisoft\Translator\Translator;
use Yiisoft\Translator\MessageFormatterInterface;
use Yiisoft\Translator\MessageReaderInterface;

final class TranslatorTest extends TestCase
{
    private function getMessages(): array
    {
        return [
            'app' => [
                'en' => [
                    'test.id1' => 'app: Test 1 on the (en)',
                    'test.id2' => 'app: Test 2 on the (en)',
                    'test.id3' => 'app: Test 3 on the (en)',
                ],
                'ua' => [
                    'test.id1' => 'app: Test 1 on the (ua)',
                ],
                'de' => [
                    'test.id1' => 'app: Test 1 on the (de)',
                    'test.id2' => 'app: Test 2 on the (de)',
                    'test.id3' => 'app: Test 3 on the (de)',
                ],
                'de-DE' => [
                    'test.id1' => 'app: Test 1 on the (de-DE)',
                    'test.id2' => 'app: Test 2 on the (de-DE)',
                ],
                'de-DE-Latin' => [
                    'test.id1' => 'app: Test 1 on the (de-DE-Latin)',
                ],
            ]
        ];
    }

    public function getTranslations(): array
    {
        return [
            ['test.id1', [], 'app', 'de', 'app: Test 1 on the (de)'],
            ['test.id2', [], 'app', 'de', 'app: Test 2 on the (de)'],
            ['test.id3', [], 'app', 'de', 'app: Test 3 on the (de)'],
            ['test.id1', [], 'app', 'de-DE', 'app: Test 1 on the (de-DE)'],
            ['test.id2', [], 'app', 'de-DE', 'app: Test 2 on the (de-DE)'],
            ['test.id3', [], 'app', 'de-DE', 'app: Test 3 on the (de)'],
            ['test.id1', [], 'app', 'de-DE-Latin', 'app: Test 1 on the (de-DE-Latin)'],
            ['test.id2', [], 'app', 'de-DE-Latin', 'app: Test 2 on the (de-DE)'],
            ['test.id3', [], 'app', 'de-DE-Latin', 'app: Test 3 on the (de)'],
        ];
    }

    public function getFallbackTranslations(): array
    {
        return [
            ['test.id1', [], 'app', 'it', 'en', 'app: Test 1 on the (en)'],
            ['test.id2', [], 'app', 'ru', 'en', 'app: Test 2 on the (en)'],
            ['test.id3', [], 'app', 'ru-RU', 'en', 'app: Test 3 on the (en)'],
        ];
    }

    public function getMissingTranslations(): array
    {
        return [
            ['test.id1', [], 'app', 'ru', 'en-US', 'test.id1'],
            ['test.id1', [], 'app2', 'de', 'en-US', 'test.id1'],
        ];
    }

    public function getTranslationsWithLocale(): array
    {
        return [
            ['test.id1', [], 'app', 'en-US', 'ua', 'app: Test 1 on the (ua)'],
            ['test.id2', [], 'app', 'en', 'de', 'app: Test 2 on the (de)'],
        ];
    }

    /**
     * @dataProvider getTranslations
     */
    public function testTranslation(
        string $id,
        array $parameters,
        string $categoryName,
        string $locale,
        string $expected
    ): void {
        $translator = new Translator(
            $this->createCategory($categoryName, $this->getMessages()),
            $locale,
            $this->createMock(EventDispatcherInterface::class),
        );
        $this->assertEquals($expected, $translator->translate($id, $parameters, $categoryName, $locale));
    }

    /**
     * @dataProvider getFallbackTranslations
     */
    public function testFallbackTranslation(
        string $id,
        array $parameters,
        string $categoryName,
        string $locale,
        string $fallbackLocale,
        string $expected
    ) {
        $translator = new Translator(
            $this->createCategory($categoryName, $this->getMessages()),
            $locale,
            $this->createMock(EventDispatcherInterface::class),
            $fallbackLocale
        );

        $this->assertEquals($expected, $translator->translate($id, $parameters, $categoryName, $locale));
    }

    /**
     * @dataProvider getMissingTranslations
     */
    public function testMissingTranslation(
        string $id,
        array $parameters,
        string $categoryName,
        string $locale,
        string $fallbackLocale,
        string $expected
    ): void {
        $eventDispatcher = $this->getMockBuilder(EventDispatcherInterface::class)->getMock();
        $eventDispatcher
            ->expects($this->any())
            ->method('dispatch')
            ->withConsecutive(
                [new MissingTranslationEvent($categoryName, $locale, $id)],
                [new MissingTranslationEvent($categoryName, $fallbackLocale, $id)],
            );

        /** @var EventDispatcherInterface $eventDispatcher */

        $translator = new Translator(
            $this->createCategory($categoryName, $this->getMessages()),
            $locale,
            $eventDispatcher
        );

        $this->assertEquals($expected, $translator->translate($id, $parameters, $categoryName, $locale));
    }

    /**
     * @dataProvider getTranslationsWithLocale
     */
    public function testTranslationSetLocale(
        string $id,
        array $parameters,
        string $categoryName,
        string $defaultLocale,
        string $locale,
        string $expected
    ) {
        $translator = new Translator(
            $this->createCategory($categoryName, $this->getMessages()),
            $defaultLocale,
            $this->createMock(EventDispatcherInterface::class),
        );

        $translator->setLocale($locale);

        $this->assertEquals($expected, $translator->translate($id, $parameters, $categoryName));
    }

    public function testTranslationMissingCategory()
    {
        $categoryName = 'miss';
        $eventDispatcher = $this->getMockBuilder(EventDispatcherInterface::class)->getMock();
        $eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(new MissingTranslationCategoryEvent($categoryName));

        /** @var EventDispatcherInterface $eventDispatcher */
        $translator = new Translator(
            $this->createCategory('app', $this->getMessages()),
            'en-US',
            $eventDispatcher
        );

        $translator->translate('miss', [], 'miss');
    }

    private function createCategory(string $categoryName, array $messages): Category
    {
        return new Category(
            $categoryName,
            $this->createMessageReader($categoryName, $messages),
            $this->createMessageFormatter()
        );
    }

    private function createMessageReader(string $category, array $messages): MessageReaderInterface
    {
        return (new class($category, $messages) implements MessageReaderInterface {
            private string $category;
            private array $messages;

            public function __construct(string $category, array $messages)
            {
                $this->category = $category;
                $this->messages = $messages;
            }

            public function getMessage(string $id, string $category, string $locale, array $parameters = []): ?string
            {
                return $this->messages[$this->category][$locale][$id] ?? null;
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
