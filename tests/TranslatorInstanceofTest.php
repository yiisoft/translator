<?php

declare(strict_types=1);

namespace Yiisoft\Translator\Tests;

use Psr\EventDispatcher\EventDispatcherInterface;
use Yiisoft\Translator\Message;
use Yiisoft\Translator\Translator;

final class TranslatorInstanceofTest  extends BaseMock
{

    public function testTranslationInstanceof(): void {
        $categoryName = 'app';
        $locale = 'en';

        $translator = new Translator(
            $this->createCategory($categoryName, [
                'app' => [
                    'en' => [
                        'test.id1' => 'translation test {id1}',
                        'test.id2' => 'translation test {id2} {id4}',
                        'test.id3' => '{id3}',
                    ],
                ],
            ]),
            $locale,
            null,
            $this->createMock(EventDispatcherInterface::class)
        );

        $translatableObject = [
            'test.id1' => new Message('test.id1', ['id1' => 10]),
            'test.id2' => new Message(
                'test.id2',
                [
                    'id2' => new Message('test.id3', ['id3' => '11']),
                    'id4' => 12,
                ]
            ),
            'test.id5' => 111,
            7 => 8,
        ];

        $this->assertEquals(
            [
                'test.id1' => 'translation test 10',
                'test.id2' => 'translation test 11 12',
                'test.id5' => 111,
                7 => 8,
            ],
            $translator->translateInstanceOf(
                $translatableObject,
                Message::class
            )
        );

        $this->assertEquals(
            'translation test 10',
            $translator->translateInstanceOf(
                new Message('test.id1', ['id1' => 10]),
                Message::class
            )
        );

        $this->assertEquals(
            'translation test 11 12',
            $translator->translateInstanceOf(
                new Message(
                    'test.id2',
                    [
                        'id2' => new Message('test.id3', ['id3' => '11']),
                        'id4' => 12,
                    ]
                ),
                Message::class
            )
        );
    }
}
