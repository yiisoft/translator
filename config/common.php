<?php

declare(strict_types=1);

use Psr\EventDispatcher\EventDispatcherInterface;
use Yiisoft\Definitions\Reference;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Translator\Translator;

/** @var array $params */

return [
    // Configure application CategorySource
    //'translation.app' => [
    //    'definition' => static function (Aliases $aliases, MessageFormatterInterface $messageFormatter) use ($params) {
    //        $messageSource = new MessageSource($aliases->get('@messages'));
    //
    //        return new CategorySource(
    //            $params['yiisoft/translator']['defaultCategory'],
    //            $messageSource,
    //            $messageFormatter,
    //        );
    //    },
    //    'tags' => ['translation.categorySource']
    //],

    TranslatorInterface::class => [
        'class' => Translator::class,
        '__construct()' => [
            $params['yiisoft/translator']['locale'],
            $params['yiisoft/translator']['fallbackLocale'],
            $params['yiisoft/translator']['defaultCategory'],
            Reference::optional(EventDispatcherInterface::class),
        ],
        'addCategorySources()' => [Reference::to('tag@translation.categorySource')],
        'reset' => function () use ($params) {
            /** @var Translator $this */
            $this->setLocale($params['yiisoft/translator']['locale']);
        },
    ],
];
