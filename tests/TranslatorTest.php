<?php

declare(strict_types=1);

namespace Yiisoft\Translator\Tests;

use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Yiisoft\Translator\Event\MissingTranslationEvent;
use Yiisoft\Translator\Translator;
use Yiisoft\Translator\MessageFormatterInterface;
use Yiisoft\Translator\MessageReaderInterface;

final class TranslatorTest extends TestCase
{
    private function getMessages(string $category = 'app', string $locale = 'de'): array
    {
        return [
            $category => [
                $locale => [
                    'test.id1' => $category . ': Test on the (' . $locale . ')'
                ]
            ]
        ];
    }

    public function getTranslations(): array
    {
        return [
            ['test.id1', [], 'app', 'de', 'app: Test on the (de)'],
            ['test.id1', [], 'app', 'de-DE', 'app: Test on the (de)'],
            ['test.id1', [], 'app', 'ru', 'test.id1'],
            ['test.id1', [], 'app2', 'de', 'test.id1'],
        ];
    }

    public function getMissingTranslations(): array
    {
        return [
            ['test.id1', [], 'app', 'ru', 'test.id1'],
            ['test.id1', [], 'app2', 'de', 'test.id1'],
        ];
    }

    /**
     * @dataProvider getTranslations
     */
    public function testTranslation(
        string $id,
        array $parameters,
        string $category,
        string $locale,
        string $expected
    ): void
    {
        $messageReader = $this->createMessageReader($this->getMessages());
        $messageFormatter = $this->createMessageFormatter();

        $translator = new Translator($category,
            $locale,
            $messageReader,
            $messageFormatter,
            $this->createMock(EventDispatcherInterface::class));

        $this->assertEquals($expected, $translator->translate($id, $parameters, $category, $locale));
    }

    /**
     * @dataProvider getMissingTranslations
     */
    public function testMissingTranslation(
        string $id,
        array $parameters,
        string $category,
        string $locale
    ): void
    {
        $messageReader = $this->createMessageReader($this->getMessages());
        $messageFormatter = $this->createMessageFormatter();
        /**
         * @var EventDispatcherInterface
         */
        $eventDispatcher = $this->getMockBuilder(EventDispatcherInterface::class)->getMock();

        $eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(new MissingTranslationEvent($category, $locale, $id));

        $translator = new Translator($category,
            $locale,
            $messageReader,
            $messageFormatter,
            $eventDispatcher);

        $translator->translate($id, $parameters, $category, $locale);
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
