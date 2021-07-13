<?php
require_once "../vendor/autoload.php";

use cms10\DataVisual\Core\Color;
use cms10\DataVisual\Format\JPEGFormat;
use cms10\DataVisual\Format\PNGFormat;
use cms10\DataVisual\Table;

$logBuf = [];
function exec_time(float $startTime, string $tag = '')
{
    global $logBuf;
    $endTime = microtime(true);
    $s = sprintf("[$tag] exec time %.3f ms\n", ($endTime - $startTime) * 1000);
    $logBuf[] = $s;
    return $endTime;
}
$start = microtime(true);

$cols = ['楼层', '姓名', '夜', '休', '白'];
$row = ['七楼', '护工003', '0', 6, '0'];
for ($i = 1; $i <= 31; $i++) {
    $cols[] = substr(100 + $i, 1, 2);
    $row[] = $i % 7 ? "空" : ['value' => '休', 'background_color' => 'rgb(68, 214, 0)', 'font_color' => '#223354'];
}

$rows = [];
for ($r = 1; $r <= 40; $r++) {
    $rows[] = $row;
}

$start = exec_time($start, 'init data');
/*$rows = [
    ['七楼', '赵梅兰', '0', '0', '0', '空', '空', '空', '空', '空', '空', '空'],
    ['七楼', '护工003', '0', 6, '0', '空', '空', ['value' => '空', 'font_color' => ''],
        ['value' => '空', 'background_color' => ''],
        ['value' => '休', 'background_color' => 'rgb(68, 214, 0)', 'font_color' => '#223354'],
        '空',
        ['value' => '休', 'background_color' => 'rgb(68, 214, 0)', 'font_color' => '#223354']
    ],
    ['七楼', '陈冬芹', '9', '0', '0',
        ['value' => '夜', 'background_color' => 'rgba(34, 51, 84, 0.5)', 'font_color' => 'rgb(34, 51, 84)'],
        ['value' => '夜', 'background_color' => 'rgba(34, 51, 84, 0.5)', 'font_color' => 'rgb(34, 51, 84)'],
        ['value' => '夜', 'background_color' => 'rgba(34, 51, 84, 0.5)', 'font_color' => 'rgb(34, 51, 84)'],
        ['value' => '夜', 'background_color' => 'rgba(34, 51, 84, 0.5)', 'font_color' => 'rgb(34, 51, 84)'],
        ['value' => '夜', 'background_color' => 'rgba(34, 51, 84, 0.5)', 'font_color' => 'rgb(34, 51, 84)'],
        ['value' => '夜', 'background_color' => 'rgba(34, 51, 84, 0.5)', 'font_color' => 'rgb(34, 51, 84)'],
        ['value' => '夜', 'background_color' => 'rgba(34, 51, 84, 0.5)', 'font_color' => 'rgb(34, 51, 84)']
    ]
];*/




$table = new Table(100, 100, [
    'x' => 0,
    'y' => 0,
    'backcolor' => 0xffffff,
    'tableMargin' => [30],
    'tableBorder' => [5],
    'headerPadding' => [10],
    'rowPadding' => [10],
    'columnFontSize' => 20,
    'rowFontSize' => 16,
    'rowColor' => 0x666666,
//    'verticalLineWidth' => 3,
//    'horizontalLineWidth' => 3,
]);

$table->setColumns($cols);
$start = exec_time($start, 'init col');
$table->setRows($rows);
$start = exec_time($start, 'init row');


try {
    $tab = $table->draw();
    $tab->canvas->frameImage((new Color('rgb(220,220,220)'))->toImagickPixel(),11,11,1,10);
    $start = exec_time($start, 'draw');

    $pic = new PNGFormat($tab);
    echo $pic->save('1.png');
//    $pic->response();
} catch (\ImagickException | \ImagickPixelException $e) {
    echo $e->getMessage();
}