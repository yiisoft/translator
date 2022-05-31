<?php

declare(strict_types=1);

namespace Yiisoft\Translator\Tests;

use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Yiisoft\Translator\CategorySource;
use Yiisoft\Translator\Event\MissingTranslationCategoryEvent;
use Yiisoft\Translator\Event\MissingTranslationEvent;
use Yiisoft\Translator\MessageFormatterInterface;
use Yiisoft\Translator\MessageReaderInterface;
use Yiisoft\Translator\Translator;
use Yiisoft\Translator\TranslatorInterface;

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
            ],
        ];
    }

    public function getTranslations(): array
    {
        return [
            ['test.id1', [], 'app', 'de', 'app: Test 1 on the (de)'],
            ['test.id2', [], 'app', 'de', 'app: Test 2 on the (de)'],
            ['test.id3', [], 'app', 'de', 'app: Test 3 on the (de)'],
            ['test.not_exists_id', [], 'app', 'de', 'test.not_exists_id'],
            ['test.id1', [], 'app', 'de-DE', 'app: Test 1 on the (de-DE)'],
            ['test.id2', [], 'app', 'de-DE', 'app: Test 2 on the (de-DE)'],
            ['test.id3', [], 'app', 'de-DE', 'app: Test 3 on the (de)'],
            ['test.not_exists_id', [], 'app', 'de-DE', 'test.not_exists_id'],
            ['', [], 'app', 'de-DE', ''],
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
            $locale,
            null,
            $this->createMock(EventDispatcherInterface::class)
        );
        $categorySource = $this->createCategory($categoryName, $this->getMessages());
        $translator->addCategorySource($categorySource);
        $this->assertEquals($expected, $translator->translate($id, $parameters, $categoryName, $locale));
    }

    public function testWithoutDefaultCategory(): void
    {
        $locale = 'en';
        $translator = new Translator($locale);
        $this->assertEquals('Without translation', $translator->translate('Without translation'));
        $this->assertEquals('Without translation', $translator->translate('Without translation', [], ''));
    }

    public function testWithoutDefaultCategoryMissingEvent(): void
    {
        $eventDispatcher = $this
            ->getMockBuilder(EventDispatcherInterface::class)
            ->getMock();
        $eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(new MissingTranslationCategoryEvent('app'));

        $locale = 'en';
        $translator = new Translator($locale, null, $eventDispatcher);
        $this->assertEquals('Without translation', $translator->translate('Without translation'));
    }

    public function testMultiCategories(): void
    {
        $locale = 'en';
        $translator = new Translator($locale);
        $translator->addCategorySource($this->createCategory('app', [
            'app' => [
                'en' => [
                    'test.id1' => 'app: Test 1 on the (en)',
                ],
            ],
        ]));
        $translator->addCategorySource($this->createCategory('app2', [
            'app2' => [
                'en' => [
                    'test.id1' => 'app2: Test 1 on the (en)',
                ],
            ],
        ]));
        $this->assertEquals('app: Test 1 on the (en)', $translator->translate('test.id1'));
        $this->assertEquals('app2: Test 1 on the (en)', $translator->translate('test.id1', [], 'app2'));
    }

    private function createTranslatorWithManySources(string $locale, string $fallbackLocale): TranslatorInterface
    {
        $translator = new Translator($locale, $fallbackLocale);

        $translator->addCategorySource($this->createCategory('app', [
            'app' => [
                'de' => [
                    'test.id5' => 'app: Test 5 on the (de). First source',
                ],
                'en' => [
                    'test.id1' => 'app: Test 1 on the (en). First source',
                    'test.id2' => 'app: Test 2 on the (en). First source',
                    'test.id3' => 'app: Test 3 on the (en). First source',
                ],
                'en-US' => [
                    'test.id1' => 'app: Test 1 on the (en-US). First source',
                    'test.id2' => 'app: Test 2 on the (en-US). First source',
                ],
            ],
        ]));

        $translator->addCategorySources([
            $this->createCategory('app', [
                'app' => [
                    'en' => [
                        'test.id1' => 'app: Test 1 on the (en). Second source',
                        'test.id2' => 'app: Test 2 on the (en). Second source',
                    ],
                ],
            ]),
            $this->createCategory('app', [
                'app' => [
                    'en-US' => [
                        'test.id1' => 'app: Test 1 on the (en-US). Third source',
                    ],
                ],
            ]),
        ]);

        return $translator;
    }

    public function manyTranslations(): array
    {
        return [
            [
                'app: Test 1 on the (en-US). Third source',
                'test.id1',
                [],
                'app',
                'en-US',
            ],
            [
                'app: Test 1 on the (en). Second source',
                'test.id1',
                [],
                'app',
                'en',
            ],
            [
                'app: Test 1 on the (en). Second source',
                'test.id1',
            ],
            [
                'app: Test 2 on the (en-US). First source',
                'test.id2',
                [],
                'app',
                'en-US',
            ],
            [
                'app: Test 2 on the (en). Second source',
                'test.id2',
                [],
                'app',
                'en',
            ],
            [
                'app: Test 3 on the (en). First source',
                'test.id3',
                [],
                'app',
                'en-US',
            ],
            [
                'app: Test 3 on the (en). First source',
                'test.id3',
                [],
                'app',
                'en',
            ],
            [
                'test.id4',
                'test.id4',
                [],
                'app',
                'en-US',
            ],
            [
                'test.id4',
                'test.id4',
                [],
                'app',
                'en',
            ],
            [
                'test.id4',
                'test.id4',
                [],
                'app',
                'de',
            ],
            [
                'test.id4',
                'test.id4',
            ],
            [
                'app: Test 5 on the (de). First source',
                'test.id5',
                [],
                'app',
                'en-US',
            ],
            [
                'app: Test 5 on the (de). First source',
                'test.id5',
                [],
                'app',
                'en',
            ],
            [
                'app: Test 5 on the (de). First source',
                'test.id5',
            ],
        ];
    }

    /**
     * @dataProvider manyTranslations
     *
     * @param string $expected
     * @param string $id
     * @param array $params
     * @param string|null $category
     * @param string|null $locale
     */
    public function testManySourcesForSingleCategory(
        string $expected,
        string $id,
        array $params = [],
        ?string $category = null,
        ?string $locale = null
    ): void {
        $translator = $this->createTranslatorWithManySources('en', 'de');

        $this->assertEquals($expected, $translator->translate($id, $params, $category, $locale));
    }

    public function testWithCategory(): void
    {
        $locale = 'en';
        $translator = new Translator($locale);
        $translator->addCategorySource($this->createCategory('app', [
            'app' => [
                'en' => [
                    'test.id1' => 'app: Test 1 on the (en)',
                ],
            ],
        ]));
        $translator->addCategorySource($this->createCategory('app2', [
            'app2' => [
                'en' => [
                    'test.id1' => 'app2: Test 1 on the (en)',
                ],
            ],
        ]));
        $this->assertEquals('app: Test 1 on the (en)', $translator->translate('test.id1'));

        $newTranslator = $translator->withCategory('app2');
        $this->assertNotSame($translator, $newTranslator);
        $this->assertEquals('app: Test 1 on the (en)', $translator->translate('test.id1'));
        $this->assertEquals('app2: Test 1 on the (en)', $newTranslator->translate('test.id1'));
    }

    public function testWithLocale(): void
    {
        $locale = 'en';
        $translator = new Translator($locale);
        $translator->addCategorySource($this->createCategory('app', []));

        $this->assertEquals($locale, $translator->getLocale());

        $newLocale = 'de';
        $newTranslator = $translator->withLocale($newLocale);

        $this->assertNotSame($translator, $newTranslator);
        $this->assertEquals($locale, $translator->getLocale());
        $this->assertEquals($newLocale, $newTranslator->getLocale());
    }

    public function testAddMultiCategorySource(): void
    {
        $locale = 'en';
        $translator = new Translator($locale);
        $translator->addCategorySources([
            $this->createCategory('app', [
                'app' => [
                    'en' => [
                        'test.id1' => 'app: Test 1 on the (en)',
                    ],
                ],
            ]),
            $this->createCategory('app2', [
                'app2' => [
                    'en' => [
                        'test.id1' => 'app2: Test 1 on the (en)',
                    ],
                ],
            ]),
            $this->createCategory('app3', [
                'app3' => [
                    'en' => [
                        'test.id1' => 'app3: Test 1 on the (en)',
                    ],
                ],
            ]),
        ]);

        $this->assertEquals('app: Test 1 on the (en)', $translator->translate('test.id1'));
        $this->assertEquals('app2: Test 1 on the (en)', $translator->translate('test.id1', [], 'app2'));
        $this->assertEquals('app3: Test 1 on the (en)', $translator->translate('test.id1', [], 'app3'));
    }

    public function testWithNotExistCategory(): void
    {
        $locale = 'en';
        $translator = new Translator($locale);
        $translator->addCategorySource($this->createCategory('app', [
            'app' => [
                'en' => [
                    'test.id1' => 'app: Test 1 on the (en)',
                ],
            ],
        ]));
        $translator->addCategorySource($this->createCategory('app2', [
            'app2' => [
                'en' => [
                    'test.id1' => 'app2: Test 1 on the (en)',
                ],
            ],
        ]));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Category with name "app3" does not exist.');

        $translator->withCategory('app3');
    }

    /**
     * @dataProvider getTranslations
     */
    public function testTranslationWithoutEventDispatcher(
        string $id,
        array $parameters,
        string $categoryName,
        string $locale,
        string $expected
    ): void {
        $translator = new Translator($locale);
        $translator->addCategorySource($this->createCategory($categoryName, $this->getMessages()));
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
    ): void {
        $translator = new Translator(
            $locale,
            $fallbackLocale,
            $this->createMock(EventDispatcherInterface::class)
        );
        $translator->addCategorySource($this->createCategory($categoryName, $this->getMessages()));

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
        $eventDispatcher = $this
            ->getMockBuilder(EventDispatcherInterface::class)
            ->getMock();
        $eventDispatcher
            ->expects($this->any())
            ->method('dispatch')
            ->withConsecutive(
                [new MissingTranslationEvent($categoryName, $locale, $id)],
                [new MissingTranslationEvent($categoryName, $fallbackLocale, $id)],
            );

        /** @var EventDispatcherInterface $eventDispatcher */

        $translator = new Translator(
            $locale,
            null,
            $eventDispatcher
        );
        $translator->addCategorySource($this->createCategory($categoryName, $this->getMessages()));

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
    ): void {
        $translator = new Translator(
            $defaultLocale,
            null,
            $this->createMock(EventDispatcherInterface::class)
        );
        $translator->addCategorySource($this->createCategory($categoryName, $this->getMessages()));
        $this->assertEquals($defaultLocale, $translator->getLocale());

        $translator->setLocale($locale);

        $this->assertEquals($locale, $translator->getLocale());

        $this->assertEquals($expected, $translator->translate($id, $parameters, $categoryName));
    }

    public function testTranslationMissingCategory(): void
    {
        $categoryName = 'miss';
        $eventDispatcher = $this
            ->getMockBuilder(EventDispatcherInterface::class)
            ->getMock();
        $eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(new MissingTranslationCategoryEvent($categoryName));

        /** @var EventDispatcherInterface $eventDispatcher */
        $translator = new Translator(
            'en-US',
            null,
            $eventDispatcher
        );
        $translator->addCategorySource($this->createCategory('app', $this->getMessages()));

        $translator->translate('miss', [], 'miss');
    }

    public function testTranslationMissingMessage(): void
    {
        $eventDispatcher = $this
            ->getMockBuilder(EventDispatcherInterface::class)
            ->getMock();
        $eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(new MissingTranslationEvent('app', 'en', 'missing_message'));

        /** @var EventDispatcherInterface $eventDispatcher */
        $translator = new Translator(
            'en',
            null,
            $eventDispatcher
        );
        $translator->addCategorySource($this->createCategory('app', $this->getMessages()));

        $translator->translate('missing_message', [], 'app');
    }

    private function createCategory(string $categoryName, array $messages = []): CategorySource
    {
        return new CategorySource(
            $categoryName,
            $this->createMessageReader($categoryName, $messages),
            $this->createMessageFormatter()
        );
    }

    private function createMessageReader(string $category, array $messages): MessageReaderInterface
    {
        return new class ($category, $messages) implements MessageReaderInterface {
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

            public function getMessages(string $category, string $locale): array
            {
                return $this->messages[$this->category][$locale] ?? [];
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
