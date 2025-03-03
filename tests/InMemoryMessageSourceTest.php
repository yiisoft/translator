<?php

declare(strict_types=1);

namespace Yiisoft\Translator\Tests;

use PHPUnit\Framework\TestCase;
use Yiisoft\Translator\InMemoryMessageSource;

final class InMemoryMessageSourceTest extends TestCase
{
    public function testBase(): void
    {
        $source = new InMemoryMessageSource();
        $source->write('app', 'ru', [
            'test' => 'тест',
            'hello' => 'привет',
        ]);

        $this->assertSame('тест', $source->getMessage('test', 'app', 'ru'));
        $this->assertNull($source->getMessage('test', 'app', 'en'));
        $this->assertSame([], $source->getMessages('app', 'en'));
        $this->assertSame(
            [
                'test' => ['message' => 'тест'],
                'hello' => ['message' => 'привет'],
            ],
            $source->getMessages('app', 'ru'),
        );
    }
}
