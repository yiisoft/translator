<?php

declare(strict_types=1);

use Psr\EventDispatcher\EventDispatcherInterface;
use Yiisoft\Definitions\Reference;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Translator\Translator;

/** @var array $params */

return [
    // Configure application CategorySource
    // 'translator.app' => static function (\Yiisoft\Translator\IntlMessageFormatter $formatter) use ($params) {
    //     return new \Yiisoft\Translator\CategorySource(
    //         $params['yiisoft/translator']['defaultCategory'],
    //         new \Yiisoft\Translator\Message\Php\MessageSource('/path/to/messages'),
    //         $formatter
    //     );
    // },

    TranslatorInterface::class => [
        'class' => Translator::class,
        '__construct()' => [
            $params['yiisoft/translator']['locale'],
            $params['yiisoft/translator']['fallbackLocale'],
            $params['yiisoft/translator']['defaultCategory'],
            Reference::optional(EventDispatcherInterface::class),
        ],
        'addCategorySources()' => [
            ...$params['yiisoft/translator']['categorySources'],
        ],
        'reset' => function () use ($params) {
            /** @var Translator $this */
            $this->setLocale($params['yiisoft/translator']['locale']);
        },
    ],
];
