<?php

declare(strict_types=1);

namespace Yiisoft\Translator\Tests;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Yiisoft\Translator\IntlMessageFormatter;

final class IntlMessageFormatterTest extends TestCase
{
    public function testException(): void
    {
        if (extension_loaded('intl')) {
            $this->markTestSkipped('The intl extension must be unavailable for this test.');
        }

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'In order to use intl message formatter, intl extension must be installed and enabled.'
        );
        new IntlMessageFormatter();
    }
}
