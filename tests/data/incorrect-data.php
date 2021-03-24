<?php

declare(strict_types=1);

return [
    [
        0 => 9,
        1 => '->translate()',
    ],
    [
        0 => 10,
        1 => '->translate(messageId1, array(1, 2))',
    ],
    [
        0 => 11,
        1 => '->translate($messageId2, [1, 2])',
    ],
    [
        0 => 12,
        1 => '->translate($messageId2->id, [1, 2])',
    ],
    [
        0 => 13,
        1 => '->translate(null, [1, 2])',
    ],
    [
        0 => 14,
        1 => '->translate([1, 2])',
    ],
    [
        0 => 15,
        1 => '->translate(fn() => \'messageId3\', array(1, 2))',
    ],
    [
        0 => 16,
        1 => '->translate(function($data) {return \'messageId4\';}, [1, 2])',
    ],
    [
        0 => 17,
        1 => '->translate()',
    ],
    [
        0 => 18,
        1 => '->translate(messageId1, function() {})',
    ],
    [
        0 => 20,
        1 => '->translate(\'messageId\' . $translator::test, function() {})',
    ],
    [
        0 => 21,
        1 => '->translate(\'messageId\' . \'1\' . $translator::test, function() {})',
    ],
    [
        0 => 22,
        1 => '->translate(\'messageId\' . 2 . $translator::test, function() {})',
    ],
    [
        0 => 23,
        1 => '->translate($translator::test, function() {})',
    ],
    [
        0 => 24,
        1 => '->translate($translator::test . \'messageId\', function() {})',
    ],
    [
        0 => 26,
        1 => '->translate(\'messageId\',
function() {]
)',
    ],
];
