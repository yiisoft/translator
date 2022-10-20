<?php

declare(strict_types=1);

namespace envs\WithIntl;

use PHPUnit\Framework\TestCase;
use Yiisoft\Translator\Translator;

final class TranslatorTest extends TestCase
{
    public function testDefaultMessageFormatterWithIntl(): void
    {
        $translator = new Translator();

        $message = $translator->translate('{count, select, 1{One} other{{count}}} monkeys', ['count' => 1]);

        $this->assertSame('One monkeys', $message);
    }
}
