<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Yiisoft\Translator\Event\MissingTranslationCategoryEvent;
use Yiisoft\Translator\Event\MissingTranslationEvent;

class EventTest extends TestCase
{
    public function testMissingTranslationEvent(): void
    {
        $event = new MissingTranslationEvent('app', 'en', 'missing_message');
        $this->assertEquals('app', $event->getCategory());
        $this->assertEquals('en', $event->getLanguage());
        $this->assertEquals('missing_message', $event->getMessage());
    }

    public function testMissingTranslationCategoryEvent(): void
    {
        $event = new MissingTranslationCategoryEvent('app');
        $this->assertEquals('app', $event->getCategory());
    }
}
