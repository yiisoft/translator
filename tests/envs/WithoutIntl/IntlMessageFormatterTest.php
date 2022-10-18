<?php

declare(strict_types=1);

namespace envs\WithoutIntl;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Yiisoft\Translator\IntlMessageFormatter;

final class IntlMessageFormatterTest extends TestCase
{
    public function testException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'In order to use intl message formatter intl extension must be installed and enabled.'
        );
        new IntlMessageFormatter();
    }
}
