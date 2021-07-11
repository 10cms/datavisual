<?php

namespace cms10\DataVisual;

use cms10\DataVisual\Core\Box;
use cms10\DataVisual\Core\Color;
use cms10\DataVisual\Format\JPEGFormat;
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
    protected int $width;
    protected int $height;

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

    protected float $borderWidth;

    protected Box $headerPadding;
    protected Box $rowPadding;
    protected Box $tableMargin;

    protected ImagickDraw $headerDraw;
    protected ImagickDraw $rowDraw;


    /**
     * Constructs the Table.
     * @param int $width The width of the Table, in pixels.
     * @param int $height The Table's height, in pixels.
     * @param array $options
     */
    public function __construct(int $width = 100, int $height = 100, array $options = [])
    {
        parent::__construct($width, $height, $options);

        $this->x = $options['x'] ?? 0;
        $this->y = $options['y'] ?? 0;

        $this->headerBaseline = 0;
        $this->rowBaseline = 0;

        $this->headerTextHeight = 0;
        $this->rowTextHeight = 0;

        $this->headerColor = new Color($options['textColor'] ?? 0x606e86);
        $this->headerBackcolor = new Color($options['columnBackcolor'] ?? 0xf6f8fb);
        $this->rowColor = new Color($options['rowColor'] ?? 0x222222);
        $this->rowBackcolor = new Color($options['rowBackcolor'] ?? 0xffffff);
        $this->borderColor = new Color($options['rowBackcolor'] ?? 0xcccccc);
        $this->textColor = new Color($options['textColor'] ?? 0x222222);

        $this->headerFont = __DIR__ . '/fonts/msyhbd.ttc';
        $this->rowFont = __DIR__ . '/fonts/msyh.ttc';

        $this->headerFontSize = $options['columnFontSize'] ?? 16;
        $this->rowFontSize = $options['rowFontSize'] ?? 12;

        $this->borderWidth = $options['borderSize'] ?? 0;
        $this->headerPadding = new Box($options['columnPadding'] ?? [0, 0, 0, 0]);
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
        $this->headerDraw->setFillColor($this->headerColor->toImagickPixel());
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

            $x = $metrics['textWidth'] + $this->headerPadding->right + $this->headerPadding->left;
            if (empty($this->columnWidths[$k]) || $this->columnWidths[$k] < $x) {
                $this->columnWidths[$k] = $x;
            }
        }

        return $this;
    }

    /**
     * @throws ImagickException
     * @throws ImagickDrawException
     * @throws ImagickPixelException
     */
    public function setRows(array $rows): TableInterface
    {
        $this->rowDraw->setFillColor($this->rowColor->toImagickPixel());
        $this->rowDraw->setFont($this->rowFont);
        $this->rowDraw->setFontSize($this->rowFontSize);
//        $this->rowDraw->setGravity(Imagick::GRAVITY_NORTH);
        $this->rowDraw->setTextAlignment(Imagick::ALIGN_LEFT);

        $this->rows = $rows;
        foreach ($this->rows as $row) {
            foreach ($row as $k => $item) {
                if (is_array($item)) {
                    $text = $item['value'];
                } else {
                    $text = $item;
                }
                // Measure the text.
                $metrics = $this->canvas->queryFontMetrics($this->rowDraw, $text);

                if ($this->rowBaseline < $metrics['boundingBox']['y2']) {
                    $this->rowBaseline = $metrics['boundingBox']['y2'];
                }
                if ($this->rowTextHeight < $metrics['textHeight'] + $metrics['descender']) {
                    $this->rowTextHeight = $metrics['textHeight'] + $metrics['descender'];
                }

                $x = $metrics['textWidth'] + $this->rowPadding->right + $this->rowPadding->left;
                if (empty($this->columnWidths[$k]) || $this->columnWidths[$k] < $x) {
                    $this->columnWidths[$k] = $x;
                }
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
        $height = $this->tableMargin->top + $this->tableMargin->bottom;
        $width = $this->tableMargin->left + $this->tableMargin->right;
        foreach ($this->columnWidths as $w) {
            $width += $w;
        }
        $height += $this->headerPadding->top + $this->headerTextHeight + $this->headerPadding->bottom;
        $height += count($this->rows) * ($this->rowPadding->top + $this->rowTextHeight + $this->rowPadding->bottom);

        $this->canvas->newImage($width, $height, $this->backcolor->toImagickPixel());

        $this->_drawHeader();
        $this->_drawRows();

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
     * @throws ImagickDrawException
     */
    private function _drawHeader()
    {
        $x = $this->x + $this->tableMargin->left;
        $this->x = $x;

        $y = $this->y + $this->tableMargin->top;
        foreach ($this->columns as $k => $column) {
            $this->headerDraw->annotation($x + $this->headerPadding->left, $y + $this->headerPadding->top + $this->headerBaseline, $column);

            $x += $this->columnWidths[$k];
        }
        $this->canvas->drawImage($this->headerDraw);
        $this->y = $y + $this->headerPadding->top + $this->headerTextHeight + $this->headerPadding->bottom;
        $this->headerDraw->clear();
    }

    /**
     * @throws ImagickException
     * @throws ImagickDrawException
     * @throws ImagickPixelException
     */
    private function _drawRows()
    {
        $y = $this->y;
        foreach ($this->rows as $row) {
            $x = $this->x;

            foreach ($row as $k => $item) {
                if (is_array($item)) {
                    $this->_drawItem($x, $y, $item, $this->columnWidths[$k]);
                } else {
                    $this->rowDraw->annotation($x + $this->rowPadding->left, $y + $this->rowPadding->top + $this->rowBaseline, $item);
                }

                $x += $this->columnWidths[$k];
            }
            $y += $this->rowPadding->top + $this->rowTextHeight + $this->rowPadding->bottom;
        }

        $this->canvas->drawImage($this->rowDraw);
        $this->rowDraw->clear();
    }

    /**
     * @throws ImagickDrawException
     */
    private function _drawItem(int $x, int $y, array $item, int $width)
    {
        if (!empty($item['background_color'])) {
            $this->rowDraw->setFillColor($item['background_color']);
            $this->rowDraw->rectangle($x, $y, $x + $width, $y + $this->rowTextHeight + $this->rowPadding->top + $this->rowPadding->bottom);
        }
        if (!empty($item['font_color'])) {
            $this->rowDraw->setFillColor($item['font_color']);
        }
        $this->rowDraw->annotation($x + $this->rowPadding->top, $y + $this->rowPadding->top + $this->rowBaseline, $item['value']);
    }
}
