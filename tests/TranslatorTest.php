<?php

declare(strict_types=1);

namespace Yiisoft\Translator\Tests;

use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Yiisoft\Translator\Event\MissingTranslationEvent;
use Yiisoft\I18n\MessageFormatterInterface;
use Yiisoft\I18n\MessageReaderInterface;
use Yiisoft\Translator\Translator;

final class TranslatorTest extends TestCase
{
    /**
     * @dataProvider getTranslations
     * @param string|null $id
     * @param string|null $translation
     * @param string $context
     * @param string|null $expected
     * @param array $parameters
     * @param string|null $category
     */
    public function testTranslation(
        ?string $id,
        ?string $translation,
        string $context,
        ?string $expected,
        array $parameters,
        ?string $category
    ): void {
        $messageReader = $this->createMessageReader([$id => $translation], $context);

        $messageFormatter = null;
        if ([] !== $parameters) {
            $messageFormatter = $this->getMockBuilder(MessageFormatterInterface::class)->getMock();
            $messageFormatter
                ->method('format')
                ->willReturn($this->formatMessage($translation, $parameters));
        }

        /**
         * @var $translator Translator
         */
        $translator = $this->getMockBuilder(Translator::class)
            ->setConstructorArgs(
                [
                    $this->createMock(EventDispatcherInterface::class),
                    $messageReader,
                    $messageFormatter
                ]
            )
            ->enableProxyingToOriginalMethods()
            ->getMock();

        $this->assertEquals($expected, $translator->translate($id, $parameters, $category));
    }

    public function testFallbackLocale(): void
    {
        $category = 'test';
        $message = 'test';
        $fallbackMessage = 'test de locale';

        /**
         * @var $translator Translator
         */
        $translator = $this->getMockBuilder(Translator::class)
            ->setConstructorArgs(
                [
                    $this->createMock(EventDispatcherInterface::class),
                    $this->createMessageReader([$message => $fallbackMessage], 'de/' . $category),
                ]
            )
            ->enableProxyingToOriginalMethods()
            ->getMock();

        $translator->setDefaultLocale('de');

        $this->assertEquals($fallbackMessage, $translator->translate($message, [], $category, 'en'));
    }

    public function testMissingEventTriggered(): void
    {
        $category = 'test';
        $language = 'en';
        $message = 'Message';

        $eventDispatcher = $this->getMockBuilder(EventDispatcherInterface::class)
            ->onlyMethods(['dispatch'])
            ->getMock();

        /**
         * @var $translator Translator
         */
        $translator = $this->getMockBuilder(Translator::class)
            ->setConstructorArgs(
                [
                    $eventDispatcher,
                    $this->createMessageReader([$message => 'test'], 'de'),
                ]
            )
            ->enableProxyingToOriginalMethods()
            ->getMock();

        $eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->willReturn(new MissingTranslationEvent($category, $language, $message));

        $translator->translate($message, [], $category, $language);
    }

    public function getTranslations(): array
    {
        return [
            ['test', 'test', '', 'test', [], null],
            ['test {param}', 'translated {param}', '', 'translated param-value', ['param' => 'param-value'], null],
        ];
    }

    private function formatMessage(string $message, array $parameters): string
    {
        foreach ($parameters as $key => $value) {
            $message = str_replace('{' . $key . '}', $value, $message);
        }

        return $message;
    }

    private function createMessageReader(array $messages, string $context): MessageReaderInterface
    {
        return new class($messages, $context) implements MessageReaderInterface {
            private array $messages = [];

            public function __construct(array $messages, string $context)
            {
                $this->messages[$context] = $messages;
            }

            public function all($context = null): array
            {
                return $this->messages[$context] ?? [];
            }

            public function one(string $id, $context = null): ?string
            {
                return $this->all($context)[$id] ?? null;
            }

            public function plural(string $id, int $count, $context = null): ?string
            {
                return $this->messages[$id] ?? null;
            }
        };
    }
}
