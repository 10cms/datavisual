<?php
require_once "../vendor/autoload.php";

use cms10\DataVisual\Format\JPEGFormat;
use cms10\DataVisual\Table;

$cols = ['楼层', '姓名', '夜', '休', '白', '01', '02', '03', '04', '05', '06', '07'];
$rows = [
    ['七楼', '赵梅兰', '0', '0', '0', '空', '空', '空', '空', '空', '空', '空'],
    ['七楼', '护工003', '0', 6, '0', '空', '空', ['value' => '空', 'font_color' => ''],
        ['value' => '空', 'background_color' => ''],
        ['value' => '休', 'background_color' => 'rgb(68, 214, 0)', 'font_color' => '#223354'],
        ['value' => '休', 'background_color' => 'rgb(68, 214, 0)', 'font_color' => '#223354'],
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
];
$imagePath = '1.jpeg';

$table = new Table(800, 600);

$table->setColumns($cols);
$table->setRows($rows);

try {
    $pic = $table->draw();

    $jpeg = new JPEGFormat($pic);
    echo $jpeg->save($imagePath);

} catch (\ImagickException | \ImagickPixelException $e) {
    echo $e->getMessage();
}