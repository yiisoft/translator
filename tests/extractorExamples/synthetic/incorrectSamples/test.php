<?php
/* @var Yiisoft\Translator\Translator $translator */

define ('messageId1', 'messageId1');
$messageId2 = 'messageId2';

// withoutCategory
translate(messageId1, array(1, 2));
$translator->translate();
$translator->translate(messageId1, array(1, 2));
$translator->translate($messageId2, [1, 2]);
$translator->translate($messageId2->id, [1, 2]);
$translator->translate(null, [1, 2]);
$translator->translate([1, 2]);
$translator->translate(fn() => 'messageId3', array(1, 2));
$translator->translate(function($data) {return 'messageId4';}, [1, 2]);
$translator->translate();
$translator->translate(messageId1, function() {});

$translator->translate('messageId' . $translator::test, function() {});
$translator->translate('messageId' . '1' . $translator::test, function() {});
$translator->translate('messageId' . 2 . $translator::test, function() {});
$translator->translate($translator::test, function() {});
$translator->translate($translator::test . 'messageId', function() {});

$translator::test();
$translator->notTranslate('Сообщение2', $translator::test(), 'Категория1');
