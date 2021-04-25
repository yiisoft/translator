<?php

declare(strict_types=1);

use Yiisoft\Factory\Definition\Reference;

return [
    'yiisoft/translator' => [
        'locale' => 'en-US',
        'fallbackLocale' => null,
        'defaultCategory' => 'app',
        'categorySources' => [
            // You can add categories to your application and your modules using `Reference::to` below
            // Reference::to(CategorySourceApplication::class),
            // Reference::to(CategoryTranslationMyModule::class),
        ],
    ],
];
