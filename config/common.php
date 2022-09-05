<?php

declare(strict_types=1);

use Psr\EventDispatcher\EventDispatcherInterface;
use Yiisoft\Definitions\Reference;
use Yiisoft\Translator\MessageReaderInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Translator\Translator;
use Yiisoft\Translator\CategorySource;
use Yiisoft\Translator\MessageFormatterInterface;
use Yiisoft\Translator\SimpleMessageFormatter;

/** @var array $params */

return [
    // Configure application CategorySource
    // ApplicationCategorySource::class => [
    //     'class' => CategorySource::class,
    //     '__construct()' => [
    //         'name' => $params['yiisoft/translator']['defaultCategory'],
    //     ],
    // ],

    'DefaultCategorySource' => static function (Psr\Container\ContainerInterface $container) use ($params) {
        return new CategorySource(
            $params['yiisoft/translator']['defaultCategory'],
            $container->get(MessageReaderInterface::class),
            $container->has(MessageFormatterInterface::class)
                ? $container->get(MessageFormatterInterface::class)
                : new SimpleMessageFormatter(),
        );
    },

    TranslatorInterface::class => [
        'class' => Translator::class,
        '__construct()' => [
            $params['yiisoft/translator']['locale'],
            $params['yiisoft/translator']['fallbackLocale'],
            Reference::to(EventDispatcherInterface::class),
        ],
        'addCategorySources()' => [
            $params['yiisoft/translator']['categorySources'],
        ],
        'reset' => function () use ($params) {
            $this->setLocale($params['yiisoft/translator']['locale']);
        },
    ],
];
