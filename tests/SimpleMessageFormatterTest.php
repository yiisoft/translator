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
            // simple
            'simple, scalar (integer)' => [
                'Test number: {number}',
                ['number' => 5],
                'Test number: 5',
            ],
            'simple, scalar (string)' => [
                'Test string: {str}',
                ['str' => 'string data'],
                'Test string: string data',
            ],
            'simple, non-scalar (array)' => [
                'Test array: {arr}',
                ['arr' => ['string data']],
                'Test array: {arr}',
            ],
            // plural
            'plural, one' => [
                '{min, plural, one{character} other{characters}}',
                ['min' => 1],
                '1 character',
            ],
            'plural, other' => [
                '{min, plural, one{character} other{characters}}',
                ['min' => 2],
                '2 characters',
            ],
            'plural, reversed options' => [
                '{min, plural, other{characters} one{character}}',
                ['min' => 1],
                '1 character',
            ],
            // not supported
            'not supported' => [
                '{min, notsupported}',
                ['min' => 1],
                '1',
            ],
            // complex
            'complex' => [
                'text1 {param1} text2 {param2, number} text3 {param3, plural, one{item} other{items}} text4 {param4} ' .
                'text5',
                ['param1' => 1, 'param2' => 2, 'param3' => 3, 'param4' => 4],
                'text1 1 text2 2 text3 3 items text4 4 text5',
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
