<?php

declare(strict_types=1);

namespace Yiisoft\Translator\Tests;

use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Yiisoft\Translator\Category;
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
                ],
                'en-US' => [
                    'test.id2' => 'app: Test 2 on the (en-US)',
                    'test.id3' => 'app: Test 3 on the (en-US)',
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

    private function getMessagesEx(string $category = 'app', string $locale = 'de-DE'): array
    {
        return [
            $category => [
                $locale => [
                    'test.id1' => $category . ': Test 1 on the (' . $locale . ')',
                    'test.id2' => $category . ': Test 2 on the (' . $locale . ')',
                    'test.id3' => $category . ': Test 3 on the (' . $locale . ')',
                ]
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
            ['test.id2', [], 'app', 'ru', 'en-US', 'app: Test 2 on the (en-US)'],
            ['test.id3', [], 'app', 'ru-RU', 'en-US', 'app: Test 3 on the (en-US)'],
        ];
    }

    public function getMissingTranslations(): array
    {
        return [
            ['test.id1', [], 'app', 'ru', 'en-US', 'app: Test 1 on the (en)'],
            ['test.id1', [], 'app2', 'de', 'en-US', 'test.id1'],
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
        $messageReader = $this->createMessageReader($this->getMessages());
        $messageFormatter = $this->createMessageFormatter();

        $translator = new Translator(
            new Category($categoryName, $messageReader, $messageFormatter),
            $this->createMock(EventDispatcherInterface::class)
        );
        $translator->setLocale($locale);
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
        $messageReader = $this->createMessageReader($this->getMessages());
        $messageFormatter = $this->createMessageFormatter();

        $translator = new Translator(
            new Category($categoryName, $messageReader, $messageFormatter),
            $this->createMock(EventDispatcherInterface::class)
        );
        $translator->setLocale($locale);
        $translator->setFallbackLocale($fallbackLocale);

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
        $messageReader = $this->createMessageReader($this->getMessages());
        $messageFormatter = $this->createMessageFormatter();

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
            new Category($categoryName, $messageReader, $messageFormatter),
            $eventDispatcher
        );
        $translator->setLocale($locale);

        $this->assertEquals($expected, $translator->translate($id, $parameters, $categoryName, $locale));
    }

    private function createMessageReader(array $messages): MessageReaderInterface
    {
        return (new class($messages) implements MessageReaderInterface {
            private array $messages;

            public function __construct(array $messages)
            {
                $this->messages = $messages;
            }

            public function getMessage(string $id, string $category, string $locale, array $parameters = []): ?string
            {
                return $this->messages[$category][$locale][$id] ?? null;
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
