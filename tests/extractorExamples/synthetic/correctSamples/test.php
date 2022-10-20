<?php
/**
 * @var Yiisoft\Translator\Translator $translator
 *
 */

$translator
    ->translate('', [])
    ->translate('');

// withoutCategory
$translator->translate('messageId1', [1, 2]);
$translator->translate('messageId2', [1, 2]);
$translator->translate('message' . 'Id3', [1, 2]);
$translator->translate('message' . 'Id4', [1, 2]);

$translator->translate('message' . 'Id' . 5, [1, 2]);
$translator->translate('message' . 'Id' . 6, [1, 2]);

$translator->translate('messageId7');
$translator->translate('messageId8');
$translator->translate('message' . 'Id9');
$translator->translate('message' . 'Id10');

$translator->translate('message' . 'Id' . 11);
$translator->translate('message' . 'Id' . 12);
$translator->translate('message' . 'Id' . 1.3);
$translator->translate('message' . 'Id' . 1.4);
$translator->translate('message' . 'Id' . 1 . '5');
$translator->translate('message' . 'Id' . 1 . '6');
// recursive translator
$translator->translate('message' . 'Id' . 1 . '7', ['test' => $translator->translate('messageId' . 18)]);

// With categoryName
$translator->translate('messageId1', [1, 2], 'categoryName');
$translator->translate('messageId2', [1, 2], 'categoryName');
$translator->translate('message' . 'Id3', [1, 2], 'categoryName');
$translator->translate('message' . 'Id4', [1, 2], 'categoryName');

$translator->translate('message' . 'Id' . 5, [1, 2], 'categoryName');
$translator->translate('message' . 'Id' . 6, [1, 2], 'categoryName');

$translator->translate('messageId7', [], 'categoryName');
$translator->translate('messageId8', [], 'categoryName');
$translator->translate('message' . 'Id9', [], 'categoryName');
$translator->translate('message' . 'Id10', [], 'categoryName');

$translator->translate('message' . 'Id' . 11, [], 'categoryName');
$translator->translate('message' . 'Id' . 12, [], 'categoryName');
$translator->translate('message' . 'Id' . 1.3, [], 'categoryName');
$translator->translate('message' . 'Id' . 1.4, [], 'categoryName');
$translator->translate('message' . 'Id' . 1 . '5', [], 'categoryName');
$translator->translate('message' . 'Id' . 1 . '6', [], 'categoryName');
// recursive translator
$translator->translate('message' . 'Id' . 1 . '7', ['test' => $translator->translate('messageId' . 19)], 'categoryName');

// With categoryName and complex params
$translator->translate('messageId1', ['1', 2], 'categoryName2');
$translator->translate('messageId2', [1, '2'], 'categoryName2');
$translator->translate('message' . 'Id3', ["1", '2'], 'categoryName2');
$translator->translate('message' . 'Id4', ["1", '2'], 'categoryName2');

$translator->translate('message' . "Id" . 5, [null, 2], 'categoryName2');
$translator->translate("message" . 'Id' . 6, [null, 2], 'categoryName2');

$translator->translate('messageId7', ['n' => 1], 'categoryName2');
$translator->translate('messageId8', ['s', 't' => 2,], 'categoryName2');
$translator->translate('message' . 'Id9', [0, 1, '2', 'n' => 3], 'categoryName2');
$translator->translate('message' . 'Id10', [[]], 'categoryName2');

$translator->translate('message' . 'Id' . 11, [[0]], 'categoryName2');
$translator->translate('message' . 'Id' . 12, [['1']], 'categoryName2');
$translator->translate('message' . 'Id' . 1.3, [[1], '2'], 'categoryName2');
$translator->translate('message' . 'Id' . 1.4, ['1', []], 'categoryName2');
$translator->translate('message' . 'Id' . 1 . '5', ['1', [2]], 'categoryName2');
$translator->translate('message' . 'Id' . 1 . '6', ['s', '1' => [2]], 'categoryName2');
// recursive translator
$translator->translate('message' . 'Id' . 1 . '7', ['test' => $translator->translate('messageId' . 18, [], 'categoryName2')], 'categoryName2');
/*
$translator->translate('message18', ['s', '1' => [2]], 'categoryName2');
*/

$translator->translate('Сообщение1', [], 'Категория1');
$translator->translate('Сообщение2', $translator::test(),
    // 345
    'Категория1');
$translator->translate->translate('Сообщение3' /* 123 */, /* 123 */ $translator::test(), 'Категория1');


