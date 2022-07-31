<?php

declare(strict_types=1);

namespace Yiisoft\Translator\Tests;

use PHPUnit\Framework\TestCase;
use Yiisoft\Translator\SimpleMessageFormatter;

class SimpleMessageFormatterTest extends TestCase
{
    public function optionsProvider(): array
    {
        return [
            [
                'Test number: 5',
                'Test number: {number}',
                ['number' => 5],
            ],
            [
                'Test string: string data',
                'Test string: {str}',
                ['str' => 'string data'],
            ],
            [
                'Test array: {arr}',
                'Test array: {arr}',
                ['arr' => ['string data']],
            ],
        ];
    }

    /**
     * @dataProvider optionsProvider
     *
     * @param string $expected
     * @param string $pattern
     * @param array $params
     */
    public function testFormat(string $expected, string $pattern, array $params): void
    {
        $formatter = new SimpleMessageFormatter();
        $result = $formatter->format($pattern, $params, 'en-US');
        $this->assertEquals($expected, $result);
    }
}
