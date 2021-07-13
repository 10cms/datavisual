<?php

namespace cms10\DataVisual;

use cms10\DataVisual\Core\Box;
use cms10\DataVisual\Core\Color;
use Imagick;
use ImagickDraw;
use ImagickDrawException;
use ImagickException;
use ImagickPixelException;

/**
 * Class Table
 * @package cms10\DataVisual
 */
class Table extends Graph implements TableInterface
{
    protected int $x;
    protected int $y;

    protected int $tableWidth;
    protected int $tableHeight;

    protected array $columns;
    protected array $rows;

    protected array $columnWidths;

    protected int $headerBaseline;
    protected int $headerTextHeight;

    protected int $rowBaseline;
    protected int $rowTextHeight;

    protected string $headerFont;
    protected string $headerFontSize;
    protected string $rowFont;
    protected string $rowFontSize;

    protected Color $headerColor;
    protected Color $headerBackcolor;
    protected Color $rowColor;
    protected Color $rowBackcolor;
    protected Color $borderColor;
    protected Color $textColor;

    protected int $verticalLineWidth;
    protected int $horizontalLineWidth;
    protected Color $verticalLineColor;
    protected Color $horizontalLineColor;

    protected Box $tableBorder;
    protected Box $headerPadding;
    protected Box $rowPadding;
    protected Box $tableMargin;

    protected ImagickDraw $headerDraw;
    protected ImagickDraw $rowDraw;


    /**
     * Constructs the Table.
     * @param int $minWidth The width of the Table, in pixels.
     * @param int $minHeight The Table's height, in pixels.
     * @param array $options
     */
    public function __construct(int $minWidth = 100, int $minHeight = 100, array $options = [])
    {
        parent::__construct($minWidth, $minHeight, $options);

        $this->x = $options['x'] ?? 0;
        $this->y = $options['y'] ?? 0;

        $this->headerBaseline = 0;
        $this->rowBaseline = 0;

        $this->headerTextHeight = 0;
        $this->rowTextHeight = 0;

        $this->headerColor = new Color($options['textColor'] ?? 0x606e86);
        $this->headerBackcolor = new Color($options['columnBackcolor'] ?? 0xeeeeee);
        $this->rowColor = new Color($options['rowColor'] ?? 0x222222);
        $this->rowBackcolor = new Color($options['rowBackcolor'] ?? 0xffffff);
        $this->borderColor = new Color($options['rowBackcolor'] ?? 0x999999);
        $this->textColor = new Color($options['textColor'] ?? 0x222222);

        $this->headerFont = __DIR__ . '/fonts/msyhbd.ttc';
        $this->rowFont = __DIR__ . '/fonts/msyh.ttc';

        $this->headerFontSize = $options['columnFontSize'] ?? 16;
        $this->rowFontSize = $options['rowFontSize'] ?? 12;

        $this->verticalLineWidth = $options['verticalLineWidth'] ?? 1;
        $this->horizontalLineWidth = $options['horizontalLineWidth'] ?? 1;
        $this->verticalLineColor = new Color($options['textColor'] ?? 0xcccccc);
        $this->horizontalLineColor = new Color($options['horizontalLineColor'] ?? 0xcccccc);

        $this->tableBorder = new Box($options['tableBorder'] ?? [1]);
        $this->headerPadding = new Box($options['headerPadding'] ?? [0, 0, 0, 0]);
        $this->rowPadding = new Box($options['rowPadding'] ?? [0, 0, 0, 0]);
        $this->tableMargin = new Box($options['tableMargin'] ?? [0, 0, 0, 0]);

        $this->headerDraw = new ImagickDraw();
        $this->rowDraw = new ImagickDraw();
    }

    /**
     * @throws ImagickException
     * @throws ImagickDrawException
     * @throws ImagickPixelException
     */
    public function setColumns(array $columns): TableInterface
    {
        $this->headerDraw->setFont($this->headerFont);
        $this->headerDraw->setFontSize($this->headerFontSize);
        $this->headerDraw->setGravity(Imagick::GRAVITY_NORTH);
        $this->headerDraw->setTextAlignment(Imagick::ALIGN_LEFT);

        $this->columns = $columns;
        foreach ($this->columns as $k => $column) {
            // Measure the text.
            $metrics = $this->canvas->queryFontMetrics($this->headerDraw, $column);

            if ($this->headerBaseline < $metrics['boundingBox']['y2']) {
                $this->headerBaseline = $metrics['boundingBox']['y2'];
            }
            if ($this->headerTextHeight < $metrics['textHeight'] + $metrics['descender']) {
                $this->headerTextHeight = $metrics['textHeight'] + $metrics['descender'];
            }

            $width = $metrics['textWidth'] + $this->headerPadding->right + $this->headerPadding->left;
            if (empty($this->columnWidths[$k]) || $this->columnWidths[$k] < $width) {
                $this->columnWidths[$k] = $width;
            }
        }

        return $this;
    }

    /**
     * @throws ImagickException
     * @throws ImagickDrawException
     */
    public function setRows(array $rows): TableInterface
    {
        $this->rows = $rows;

        $this->rowDraw->setFont($this->rowFont);
        $this->rowDraw->setFontSize($this->rowFontSize);
        $this->rowDraw->setTextAlignment(Imagick::ALIGN_LEFT);

        // Gets the longest item for each column
        $maxSizeSample = [];
        for ($i = 0; $i < count($this->columns); $i++) {
            $col = array_column($this->rows, $i);
            $maxItem = '';
            $maxSize = 0;
            foreach ($col as $item) {
                if (is_array($item)) {
                    $text = $item['value'];
                } else {
                    $text = $item;
                }
                $size = strlen($text);
                if ($size > $maxSize) {
                    $maxSize = $size;
                    $maxItem = $text;
                }

            }
            $maxSizeSample[] = $maxItem;
        }
        foreach ($maxSizeSample as $k => $item) {
            // Measure the text.
            $metrics = $this->canvas->queryFontMetrics($this->rowDraw, $item);

            if ($this->rowBaseline < $metrics['boundingBox']['y2']) {
                $this->rowBaseline = $metrics['boundingBox']['y2'];
            }
            if ($this->rowTextHeight < $metrics['textHeight'] + $metrics['descender']) {
                $this->rowTextHeight = $metrics['textHeight'] + $metrics['descender'];
            }

            $width = $metrics['textWidth'] + $this->rowPadding->right + $this->rowPadding->left;
            if (empty($this->columnWidths[$k]) || $this->columnWidths[$k] < $width) {
                $this->columnWidths[$k] = $width;
            }
        }

        return $this;
    }

    /**
     * Adds a new row to the Table.
     * @param array $row
     * @return TableInterface
     */
    public function addRow(array $row): TableInterface
    {
        $this->rows[] = $row;
        return $this;
    }

    private function getKeys(array $columns): array
    {
        $keys = [];
        foreach ($columns as $colName => $value) {
            $pluck = array_column($this->rows, $colName);
            $anyKeys = array_keys(array_intersect($pluck, [$value]));
            if (!empty($keys)) {
                $keys = array_filter(array_intersect($anyKeys, $keys));
            } else {
                $keys = $anyKeys;
            }
        }
        return $keys;
    }

    /**
     * Removes the specified row.
     * @param array $columns
     * @return TableInterface
     */
    public function removeRow(array $columns): TableInterface
    {
        $keys = $this->getKeys($columns);
        foreach ($keys as $key) {
            unset($this->rows[$key]);
        }

        return $this;
    }

    /**
     * @throws ImagickException
     * @throws ImagickPixelException|ImagickDrawException
     */
    public function draw(): Table
    {
        // calculate table's width and height.
        $this->tableWidth = $this->tableMargin->left + $this->tableMargin->right + $this->tableBorder->left + $this->tableBorder->right;
        $this->tableWidth += array_sum($this->columnWidths);
        $this->tableWidth += (count($this->columnWidths) - 1) * $this->verticalLineWidth;
        if ($this->canvasWidth < $this->tableWidth) {
            $this->canvasWidth = $this->tableWidth;
        }

        $this->tableHeight = $this->tableMargin->top + $this->tableMargin->bottom + $this->tableBorder->top + $this->tableBorder->bottom;
        $this->tableHeight += $this->headerPadding->top + $this->headerTextHeight + $this->headerPadding->bottom;
        $rowCount = count($this->rows);
        $this->tableHeight += $rowCount * ($this->rowPadding->top + $this->rowTextHeight + $this->rowPadding->bottom);
        $this->tableHeight += ($rowCount - 1) * $this->horizontalLineWidth;
        if ($this->canvasHeight < $this->tableHeight) {
            $this->canvasHeight = $this->tableHeight;
        }

        $this->canvas->newImage($this->canvasWidth, $this->canvasHeight, $this->backcolor->toImagickPixel());

        $this->_drawBorder();
        $this->_drawHeader();
        $this->_drawRows();
        $this->_drawVerticalLine();
        $this->_drawHorizontalLine();
        return $this;
    }

    /**
     * Returns the rows that satisfy the condition.
     * @param array $columns conditions like ['col1' => 'value1', 'col2' => 'value2']
     * @return array
     */
    public function query(array $columns): array
    {
        $keys = $this->getKeys($columns);
        $result = [];
        foreach ($keys as $key) {
            $result[] = $this->rows[$key];
        }

        return $result;
    }

    /**
     * @throws ImagickException
     * @throws ImagickPixelException
     * @throws ImagickDrawException
     */
    private function _drawBorder()
    {
        $borderDraw = new ImagickDraw();
        $borderDraw->setFillColor($this->borderColor->toImagickPixel());
        for ($x = $this->x + $this->tableMargin->left; $x < $this->x + $this->tableWidth - $this->tableMargin->right; $x++) {
            for ($lineHeight = 0; $lineHeight < $this->tableBorder->top; $lineHeight++) {
                $borderDraw->color($x, $this->y + $this->tableMargin->top + $lineHeight, Imagick::PAINT_POINT);
            }
            for ($lineHeight = 0; $lineHeight < $this->tableBorder->bottom; $lineHeight++) {
                $borderDraw->color($x, $this->y + $this->tableHeight - $this->tableMargin->bottom - $lineHeight - 1, Imagick::PAINT_POINT);
            }
        }
        for ($y = $this->y + $this->tableMargin->top; $y < $this->y + $this->tableHeight - $this->tableMargin->bottom; $y++) {
            for ($lineWidth = 0; $lineWidth < $this->tableBorder->left; $lineWidth++) {
                $borderDraw->color($this->x + $this->tableMargin->left + $lineWidth, $y, Imagick::PAINT_POINT);
            }
            for ($lineWidth = 0; $lineWidth < $this->tableBorder->bottom; $lineWidth++) {
                $borderDraw->color($this->x + $this->tableWidth - $this->tableMargin->right - $lineWidth - 1, $y, Imagick::PAINT_POINT);
            }
        }
        $this->canvas->drawImage($borderDraw);
        $borderDraw->destroy();
    }

    /**
     * @throws ImagickException
     * @throws ImagickDrawException
     * @throws ImagickPixelException
     */
    private function _drawHeader()
    {
        $x = $this->x + $this->tableMargin->left + $this->tableBorder->left;
        $y = $this->y + $this->tableMargin->top + $this->tableBorder->top;

        $this->headerDraw->setFillColor($this->headerBackcolor->toImagickPixel());
        $this->headerDraw->rectangle($x, $y, $this->x + $this->tableWidth - $this->tableMargin->right - $this->tableBorder->right - 1, $y + $this->headerTextHeight + $this->headerPadding->top + $this->headerPadding->bottom - 1);
        $this->headerDraw->setFillColor($this->headerColor->toImagickPixel());

        foreach ($this->columns as $k => $column) {
            $this->headerDraw->annotation($x + $this->headerPadding->left, $y + $this->headerPadding->top + $this->headerBaseline, $column);

            $x += $this->columnWidths[$k] + $this->verticalLineWidth;
        }
        $this->canvas->drawImage($this->headerDraw);
        $this->headerDraw->destroy();
    }

    /**
     * @throws ImagickException
     * @throws ImagickDrawException
     * @throws ImagickPixelException
     */
    private function _drawRows()
    {
        $y = $this->y + $this->tableMargin->top + $this->tableBorder->top + $this->headerPadding->top + $this->headerTextHeight + $this->headerPadding->bottom;;
        foreach ($this->rows as $row) {
            $x = $this->x + $this->tableMargin->left + $this->tableBorder->left;
            foreach ($row as $k => $item) {
                $this->rowDraw->setFillColor($this->rowColor->toImagickPixel());
                if (is_array($item)) {
                    $this->_drawItem($x, $y, $item, $this->columnWidths[$k]);
                } else {
//                    $this->rowDraw->annotation($x + $this->rowPadding->left, $y + $this->rowPadding->top + $this->rowBaseline, $item);
                    $this->canvas->annotateImage($this->rowDraw, $x + $this->rowPadding->left, $y + $this->rowPadding->top + $this->rowBaseline, 0, $item);
                }
                $x += $this->columnWidths[$k] + $this->verticalLineWidth;
            }
            $y += $this->rowPadding->top + $this->rowTextHeight + $this->rowPadding->bottom + $this->horizontalLineWidth;
        }

        $this->canvas->drawImage($this->rowDraw);
        $this->rowDraw->destroy();
    }

    /**
     * @throws ImagickDrawException
     */
    private function _drawItem(int $x, int $y, array $item, int $width)
    {
        if (!empty($item['background_color'])) {
            $this->rowDraw->setFillColor($item['background_color']);
            $this->rowDraw->rectangle($x, $y, $x + $width - 1, $y + $this->rowTextHeight + $this->rowPadding->top + $this->rowPadding->bottom - 1);
        }
        if (!empty($item['font_color'])) {
            $this->rowDraw->setFillColor($item['font_color']);
        }
        $this->rowDraw->annotation($x + $this->rowPadding->left, $y + $this->rowPadding->top + $this->rowBaseline, $item['value']);
    }

    /**
     * @throws ImagickException
     * @throws ImagickPixelException
     * @throws ImagickDrawException
     */
    private function _drawVerticalLine()
    {
        if ($this->verticalLineWidth > 0) {
            $lineDraw = new ImagickDraw();
            $lineDraw->setFillColor($this->verticalLineColor->toImagickPixel());
            for ($y = $this->y + $this->tableMargin->top + $this->tableBorder->top; $y < $this->y + $this->tableHeight - $this->tableMargin->bottom - $this->tableBorder->bottom; $y++) {
                $x = $this->x + $this->tableMargin->left + $this->tableBorder->left;

                for ($k = 0; $k < count($this->columnWidths) - 1; $k++) {
                    $x += $this->columnWidths[$k];
                    for ($lineWidth = 0; $lineWidth < $this->verticalLineWidth; $lineWidth++) {
                        $lineDraw->color($x + $lineWidth, $y, Imagick::PAINT_POINT);
                    }
                    $x += $this->verticalLineWidth;
                }
            }
            $this->canvas->drawImage($lineDraw);
            $lineDraw->destroy();
        }
    }

    /**
     * @throws ImagickException
     * @throws ImagickPixelException
     * @throws ImagickDrawException
     */
    private function _drawHorizontalLine()
    {
        if ($this->horizontalLineWidth > 0) {
            $lineDraw = new ImagickDraw();
            $lineDraw->setFillColor($this->horizontalLineColor->toImagickPixel());
            for ($x = $this->x + $this->tableMargin->left + $this->tableBorder->left; $x < $this->x + $this->tableWidth - $this->tableMargin->right - $this->tableBorder->right; $x++) {
                $y = $this->y + $this->tableMargin->top + $this->tableBorder->top + $this->headerTextHeight + $this->headerPadding->top + $this->headerPadding->bottom;
                for ($lineWidth = 0; $lineWidth < $this->horizontalLineWidth; $lineWidth++) {
                    $lineDraw->color($x, $y + $lineWidth, Imagick::PAINT_POINT);
                }

                for ($k = 0; $k < count($this->rows) - 1; $k++) {
                    $y += $this->rowTextHeight + $this->rowPadding->top + $this->rowPadding->bottom;
                    for ($lineWidth = 0; $lineWidth < $this->horizontalLineWidth; $lineWidth++) {
                        $lineDraw->color($x, $y + $lineWidth, Imagick::PAINT_POINT);
                    }
                    $y += $this->horizontalLineWidth;
                }
            }
            $this->canvas->drawImage($lineDraw);
            $lineDraw->destroy();
        }
    }

}
