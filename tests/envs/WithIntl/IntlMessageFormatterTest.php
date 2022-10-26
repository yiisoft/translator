<?php

declare(strict_types=1);

namespace Yiisoft\Translator\Tests\envs\WithIntl;

use PHPUnit\Framework\TestCase;
use Yiisoft\Translator\IntlMessageFormatter;

final class IntlMessageFormatterTest extends TestCase
{
    private const N = 'n';
    private const N_VALUE = 42;
    private const F = 'f';
    private const F_VALUE = 2e+8;
    private const F_VALUE_FORMATTED = '200,000,000';
    private const D = 'd';
    private const D_VALUE = 200000000.101;
    private const D_VALUE_FORMATTED = '200,000,000.101';
    private const D_VALUE_FORMATTED_INTEGER = '200,000,000';
    private const SUBJECT = 'сабж';
    private const SUBJECT_VALUE = 'Answer to the Ultimate Question of Life, the Universe, and Everything';

    public function simplePatterns(): array
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
                '42-two',
                '{n, selectordinal, one{#-one} two{#-two} few{#-few} other{#-other}}',
                ['n' => 42],
            ],
        ];
    }

    public function patterns(): array
    {
        return [
            [
                self::SUBJECT_VALUE . ' is ' . self::N_VALUE, // expected
                '{' . self::SUBJECT . '} is {' . self::N . ', number}', // pattern
                [ // params
                    self::N => self::N_VALUE,
                    self::SUBJECT => self::SUBJECT_VALUE,
                ],
            ],

            [
                self::SUBJECT_VALUE . ' is ' . self::N_VALUE, // expected
                '{' . self::SUBJECT . '} is {' . self::N . ', number, integer}', // pattern
                [ // params
                    self::N => self::N_VALUE,
                    self::SUBJECT => self::SUBJECT_VALUE,
                ],
            ],

            [
                'Here is a big number: ' . self::F_VALUE_FORMATTED, // expected
                'Here is a big number: {' . self::F . ', number}', // pattern
                [ // params
                    self::F => self::F_VALUE,
                ],
            ],


            [
                'Here is a big number: ' . self::F_VALUE_FORMATTED, // expected
                'Here is a big number: {' . self::F . ', number, integer}', // pattern
                [ // params
                    self::F => self::F_VALUE,
                ],
            ],

            [
                'Here is a big number: ' . self::D_VALUE_FORMATTED, // expected
                'Here is a big number: {' . self::D . ', number}', // pattern
                [ // params
                    self::D => self::D_VALUE,
                ],
            ],

            [
                'Here is a big number: ' . self::D_VALUE_FORMATTED_INTEGER, // expected
                'Here is a big number: {' . self::D . ', number, integer}', // pattern
                [ // params
                    self::D => self::D_VALUE,
                ],
            ],

            // This one was provided by Aura.Intl. Thanks!
            [
                'ralph invites beep and 3 other people to his party.',
                <<<'_MSG_'
{gender_of_host, select,
  female {{num_guests, plural, offset:1
      =0 {{host} does not give a party.}
      =1 {{host} invites {guest} to her party.}
      =2 {{host} invites {guest} and one other person to her party.}
     other {{host} invites {guest} and # other people to her party.}}}
  male {{num_guests, plural, offset:1
      =0 {{host} does not give a party.}
      =1 {{host} invites {guest} to his party.}
      =2 {{host} invites {guest} and one other person to his party.}
     other {{host} invites {guest} and # other people to his party.}}}
  other {{num_guests, plural, offset:1
      =0 {{host} does not give a party.}
      =1 {{host} invites {guest} to their party.}
      =2 {{host} invites {guest} and one other person to their party.}
      other {{host} invites {guest} and # other people to their party.}}}}
_MSG_
                ,
                [
                    'gender_of_host' => 'male',
                    'num_guests' => 4,
                    'host' => 'ralph',
                    'guest' => 'beep',
                ],
            ],

            [
                'Alexander is male and he loves Yii!',
                '{name} is {gender} and {gender, select, female{she} male{he} other{it}} loves Yii!',
                [
                    'name' => 'Alexander',
                    'gender' => 'male',
                ],
            ],

            // verify pattern in select does not get replaced
            [
                'Alexander is male and he loves Yii!',
                '{name} is {gender} and {gender, select, female{she} male{he} other{it}} loves Yii!',
                [
                    'name' => 'Alexander',
                    'gender' => 'male',
                    // following should not be replaced
                    'he' => 'wtf',
                    'she' => 'wtf',
                    'it' => 'wtf',
                ],
            ],

            // verify pattern in select message gets replaced
            [
                'Alexander is male and wtf loves Yii!',
                '{name} is {gender} and {gender, select, female{she} male{{he}} other{it}} loves Yii!',
                [
                    'name' => 'Alexander',
                    'gender' => 'male',
                    'he' => 'wtf',
                    'she' => 'wtf',
                ],
            ],

            // some parser specific verifications
            [
                'male and wtf loves 42 is male!',
                '{gender} and {gender, select, female{she} male{{he}} other{it}} loves {nr, number} is {gender}!',
                [
                    'nr' => 42,
                    'gender' => 'male',
                    'he' => 'wtf',
                    'she' => 'wtf',
                ],
            ],

            // formatting a message that contains params but they are not provided.
            [
                'Incorrect password (length must be from {min} to {max} symbols).',
                'Incorrect password (length must be from {min, number} to {max, number} symbols).',
                ['attribute' => 'password'],
            ],

            // test ICU version compatibility
            [
                'Showing <b>{begin, number}-{end, number}</b> of <b>{totalCount, number}</b> {totalCount, plural, one{item} other{items}}.',
                'Showing <b>{begin, number}-{end, number}</b> of <b>{totalCount, number}</b> {totalCount, plural, one{item} other{items}}.',
                [],
            ],
            [
                'Showing <b>1-10</b> of <b>12</b> items.',
                'Showing <b>{begin, number}-{end, number}</b> of <b>{totalCount, number}</b> {totalCount, plural, one{item} other{items}}.',
                [// A
                    'begin' => 1,
                    'end' => 10,
                    'count' => 10,
                    'totalCount' => 12,
                    'page' => 1,
                    'pageCount' => 2,
                ],
            ],
            [
                'Showing <b>1-1</b> of <b>1</b> item.',
                'Showing <b>{begin, number}-{end, number}</b> of <b>{totalCount, number}</b> {totalCount, plural, one{item} other{items}}.',
                [// B
                    'begin' => 1,
                    'end' => 1,
                    'count' => 1,
                    'totalCount' => 1,
                    'page' => 1,
                    'pageCount' => 1,
                ],
            ],
            [
                'Showing <b>0-0</b> of <b>0</b> items.',
                'Showing <b>{begin, number}-{end, number}</b> of <b>{totalCount, number}</b> {totalCount, plural, one{item} other{items}}.',
                [// C
                    'begin' => 0,
                    'end' => 0,
                    'count' => 0,
                    'totalCount' => 0,
                    'page' => 1,
                    'pageCount' => 1,
                ],
            ],
            [
                'Total <b>{count, number}</b> {count, plural, one{item} other{items}}.',
                'Total <b>{count, number}</b> {count, plural, one{item} other{items}}.',
                [],
            ],
            [
                'Total <b>1</b> item.',
                'Total <b>{count, number}</b> {count, plural, one{item} other{items}}.',
                [
                    'count' => 1,
                ],
            ],
            [
                'Total <b>1</b> item.',
                'Total <b>{count, number}</b> {count, plural, one{item} other{items}}.',
                [
                    'begin' => 5,
                    'count' => 1,
                    'end' => 10,
                ],
            ],
            [
                '{0, plural, one {offer} other {offers}}',
                '{0, plural, one {offer} other {offers}}',
                [],
            ],
            [
                'offers',
                '{0, plural, one {offer} other {offers}}',
                [0],
            ],
            [
                'offer',
                '{0, plural, one {offer} other {offers}}',
                [1],
            ],
            [
                'offers',
                '{0, plural, one {offer} other {offers}}',
                [13],
            ],
            //            [
            //                '', // Message pattern is invalid
            //                'Message without {closing} {brace',
            //                ['closing brace and with'],
            //            ],
            [
                'Уважаемый Vadim,',
                '{gender, select, female{Уважаемая} other{Уважаемый}} {firstname},',
                [
                    'gender' => null,
                    'firstname' => 'Vadim',
                ],
            ],
        ];
    }

    public function parsePatterns(): array
    {
        return [
            [
                self::SUBJECT_VALUE . ' is ' . self::N_VALUE, // expected
                self::SUBJECT_VALUE . ' is {0, number}', // pattern
                [ // params
                    0 => self::N_VALUE,
                ],
            ],

            [
                self::SUBJECT_VALUE . ' is ' . self::N_VALUE, // expected
                self::SUBJECT_VALUE . ' is {' . self::N . ', number}', // pattern
                [ // params
                    self::N => self::N_VALUE,
                ],
            ],

            [
                self::SUBJECT_VALUE . ' is ' . self::N_VALUE, // expected
                self::SUBJECT_VALUE . ' is {' . self::N . ', number, integer}', // pattern
                [ // params
                    self::N => self::N_VALUE,
                ],
            ],

            [
                '4,560 monkeys on 123 trees make 37.073 monkeys per tree',
                '{0,number,integer} monkeys on {1,number,integer} trees make {2,number} monkeys per tree',
                [
                    0 => 4560,
                    1 => 123,
                    2 => 37.073,
                ],
                'en-US',
            ],

            [
                '4.560 Affen auf 123 Bäumen sind 37,073 Affen pro Baum',
                '{0,number,integer} Affen auf {1,number,integer} Bäumen sind {2,number} Affen pro Baum',
                [
                    0 => 4560,
                    1 => 123,
                    2 => 37.073,
                ],
                'de',
            ],

            [
                '4,560 monkeys on 123 trees make 37.073 monkeys per tree',
                '{monkeyCount,number,integer} monkeys on {trees,number,integer} trees make {monkeysPerTree,number} monkeys per tree',
                [
                    'monkeyCount' => 4560,
                    'trees' => 123,
                    'monkeysPerTree' => 37.073,
                ],
                'en-US',
            ],

            [
                '4.560 Affen auf 123 Bäumen sind 37,073 Affen pro Baum',
                '{monkeyCount,number,integer} Affen auf {trees,number,integer} Bäumen sind {monkeysPerTree,number} Affen pro Baum',
                [
                    'monkeyCount' => 4560,
                    'trees' => 123,
                    'monkeysPerTree' => 37.073,
                ],
                'de',
            ],
        ];
    }

    /**
     * @dataProvider simplePatterns
     */
    public function testSimpleFormat(string $expected, string $pattern, array $params): void
    {
        $formatter = new IntlMessageFormatter();
        $result = $formatter->format($pattern, $params, 'en-US');
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider patterns
     */
    public function testNamedArguments(string $expected, string $pattern, array $args): void
    {
        $formatter = new IntlMessageFormatter();
        $result = $formatter->format($pattern, $args, 'en-US');
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider parsePatterns
     */
    public function testParseNamedArguments(
        string $expected,
        string $pattern,
        array $args,
        string $locale = 'en-US'
    ): void {
        $formatter = new IntlMessageFormatter();
        $result = $formatter->format($pattern, $args, $locale);
        $this->assertEquals($expected, $result);
    }
}
