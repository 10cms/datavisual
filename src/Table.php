<?php

namespace cms10\DataVisual;

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
    protected array $columns;
    protected array $columnWidth;
    protected array $rows;

    protected string $columnFont;
    protected string $columnFontSize;
    protected string $rowFont;
    protected string $rowFontSize;

    protected Color $columnColor;
    protected Color $columnBackcolor;
    protected Color $rowBackcolor;
    protected Color $borderColor;
    protected Color $textColor;

    protected int $borderSize;

    /**
     * Constructs the Table.
     * @param int $width The width of the Table, in pixels.
     * @param int $height The Table's height, in pixels.
     * @param array $options
     */
    public function __construct(int $width = 100, int $height = 100, array $options = [])
    {
        parent::__construct($width, $height, $options);

        $this->columnColor = new Color($options['textColor'] ?? 0x606e86);
        $this->columnBackcolor = new Color($options['columnBackcolor'] ?? 0xf6f8fb);
        $this->rowBackcolor = new Color($options['rowBackcolor'] ?? 0xffffff);
        $this->borderColor = new Color($options['rowBackcolor'] ?? 0xcccccc);
        $this->textColor = new Color($options['textColor'] ?? 0x222222);

        $this->borderSize = $options['borderSize'] ?? 0;

        $this->columnFont = __DIR__ . '/fonts/msyhbd.ttc';
        $this->rowFont = __DIR__ . '/fonts/Open_Sans/OpenSans-Regular.ttf';

        $this->columnFontSize = $options['columnFontSize'] ?? 16;
        $this->rowFontSize = $options['rowFontSize'] ?? 12;
    }

    public function setColumns(array $columns): TableInterface
    {
        $this->columns = $columns;

        return $this;
    }

    public function setRows(array $rows): TableInterface
    {
        $this->rows = $rows;

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
     * @throws ImagickPixelException
     */
    public function draw(): Table
    {
        $this->canvas->newImage($this->width, $this->height, $this->backcolor->toImagickPixel());

        $this->_drawColumn();
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
     * @throws ImagickPixelException
     */
    private function _drawColumn()
    {
        $draw = new ImagickDraw();

        $draw->setFillColor($this->columnColor->toImagickPixel());
        $draw->setFont($this->columnFont);
        $draw->setTextEncoding('UTF-8');
        $draw->setFontSize( $this->columnFontSize);
        $draw->setGravity(Imagick::GRAVITY_NORTH);
        $draw->setTextAlignment (Imagick::ALIGN_LEFT);

        $x = 10;
        $y = 10;
        foreach ($this->columns as $column) {
            echo $column;
            // Measure the text.
            $metrics = $this->canvas->queryFontMetrics($draw, $column);

            $baseline = $metrics['boundingBox']['y2'];
//            $textWidth = $metrics['textWidth'] + 2 * $metrics['boundingBox']['x1'];
//            $textHeight = $metrics['textHeight'] + $metrics['descender'];
//            $draw->annotation (0, $baseline, $column);
            $textWidth = $metrics['textWidth'];
            $this->columnWidth[$column] = $metrics['textWidth'];


            echo $draw->annotation($x, $baseline + 20, $column);

            $x += $textWidth + 10;
        }

//        $draw->setFillColor($this->columnBackcolor->toImagickPixel());
//        $draw->rectangle(10,10,790, 590);

        $this->canvas->drawImage($draw);

        $draw->clear();
    }

    private function _drawRows()
    {
        // todo
    }
}
