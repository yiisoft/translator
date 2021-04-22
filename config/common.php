<?php

declare(strict_types=1);

use Psr\EventDispatcher\EventDispatcherInterface;
use Yiisoft\Factory\Definition\Reference;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Translator\Translator;

/** @var array $params */

return [
    TranslatorInterface::class => [
        'class' => Translator::class,
        '__construct()' => [
            $params['yiisoft/translator']['locale'],
            $params['yiisoft/translator']['fallbackLocale'],
            Reference::to(EventDispatcherInterface::class),
        ],
        'addCategorySources()' => [
            [
                // You can add categories to your application and your modules using `Reference::to` below
                // Reference::to(CategorySourceApplication::class),
                // Reference::to(CategoryTranslationMyModule::class),
            ],
        ],
    ],
];
