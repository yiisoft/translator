<?php

declare(strict_types=1);

use Yiisoft\Definitions\Reference;

return [
    'yiisoft/translator' => [
        'locale' => 'en-US',
        'fallbackLocale' => null,
        'defaultCategory' => 'app',
        'categorySources' => [
            // You can add categories from your application and additional modules using `Reference::to` below
            // Reference::to(ApplicationCategorySource::class),
            // Reference::to(MyModuleCategorySource::class),
        ],
    ],
];
