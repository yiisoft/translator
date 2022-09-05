<?php

declare(strict_types=1);

namespace Yiisoft\Translator\Tests;

use PHPUnit\Framework\TestCase;
use Yiisoft\Translator\SimpleMessageFormatter;

class SimpleMessageFormatterTest extends TestCase
{
    public function formatProvider(): array
    {
        return [
            [
                'Test number: {number}',
                ['number' => 5],
                'Test number: 5',
            ],
            [
                'Test string: {str}',
                ['str' => 'string data'],
                'Test string: string data',
            ],
            [
                'Test array: {arr}',
                ['arr' => ['string data']],
                'Test array: {arr}',
            ],
            // plural
            [
                '{min} {min, number} {min, plural, one{character} other{characters}} {max}',
                ['min' => 2, 'max' => 1],
                '2 2 2 characters 1',
            ],
        ];
    }

    /**
     * @dataProvider formatProvider
     */
    public function testFormat(string $pattern, array $params, string $expected): void
    {
        $formatter = new SimpleMessageFormatter();
        $result = $formatter->format($pattern, $params, 'en-US');
        $this->assertEquals($expected, $result);
    }
}
