<?php

namespace cms10\DataVisual;

use cms10\DataVisual\Core\Color;
use Imagick;
use ImagickDraw;
use ImagickDrawException;
use ImagickException;
use ImagickPixelException;

/**
 * Class PieChart
 * @package cms10\DataVisual
 */
class PieChart extends Chart
{
    protected array $slices;

    protected string $title;

    protected bool $hasLegend;

    protected string $titleFont;

    protected string $legendFont;

    protected Color $textColor;


    /**
     * Constructs the Chart.
     * @param int $minWidth The width of the chart, in pixels.
     * @param int $minHeight The chart's height, in pixels.
     * @param array $options
     */
    public function __construct(int $minWidth = 100, int $minHeight = 100, array $options = [])
    {
        parent::__construct($minWidth, $minHeight, $options);
    }

    /**
     * @throws ImagickException
     * @throws ImagickPixelException|ImagickDrawException
     */
    public function draw()
    {
        $this->canvas->newImage($this->canvasWidth, $this->canvasHeight, $this->backcolor->toImagickPixel());

        $total = 0;
        $sliceStart = -90;  // Start at 12 o'clock.

        $titleHeight = $this->_drawTitle();
        $legendWidth = $this->_drawLegend($titleHeight);

        // Account for the space occupied by the legend and its padding.
        $pieCenterX = ($this->canvasWidth - $legendWidth) / 2;

        // Account for the space occupied by the title.
        $pieCenterY = $titleHeight + ($this->canvasHeight - $titleHeight) / 2;

        // Give the pie 7.5% padding on either side.
        $pieDiameter = round(
            min($this->canvasWidth - $legendWidth, $this->canvasHeight - $titleHeight) * 0.85
        );

        $pieRadius = $pieDiameter / 2;

        foreach ($this->slices as $slice)
            $total += $slice['value'];

        // Draw the slices.
        foreach ($this->slices as $slice) {
            $sliceWidth = 360 * $slice['value'] / $total;

            // Skip slices that are too small to draw / be visible.
            if ($sliceWidth == 0) continue;

            $sliceEnd = $sliceStart + $sliceWidth;

            $this->_drawSlice(
                array('x' => $pieCenterX, 'y' => $pieCenterY),
                $pieRadius,
                $sliceStart,
                $sliceEnd,
                $slice['color']
            );

            // Move along to the next slice.
            $sliceStart = $sliceEnd;
        }
    }

    /**
     * @throws ImagickException
     * @throws ImagickDrawException
     * @throws ImagickPixelException
     */
    protected function _drawLegend($legendOffset)
    {
        if (!$this->hasLegend)
            return 0;

        // Determine the ideal font size for the legend text;
        $legendFontSize = $this->canvasWidth * 0.0325;

        // If the legend's font size is too small, we won't bother drawing it.
        if (ceil($legendFontSize) < 8)
            return 0;

        // Calculate the size and padding for the color squares.
        $squareSize = $this->canvasHeight * 0.060;
        $squarePadding = $this->canvasHeight * 0.025;
        $labelPadding = $this->canvasHeight * 0.025;

        $sliceCount = count($this->slices);

        $legendPadding = 0.075 * $this->canvasWidth;

        // Get the width of the legend's widest label.
        $maxLabelWidth = $this->_maxLabelWidth($legendFontSize);

        // Determine the width and height of the legend.
        $legendWidth = $squareSize + $labelPadding + $maxLabelWidth;
        $legendHeight = $sliceCount * ($squareSize + $squarePadding) - $squarePadding;

        // If the legend and its padding occupy too much space, we will not draw it.
        if ($legendWidth + $legendPadding * 2 > $this->canvasWidth / 2)  // Too wide.
            return 0;

        if ($legendHeight > $this->canvasHeight - $legendOffset - $legendPadding * 2)  // Too high.
            return 0;

        $legendX = $this->canvasWidth - $legendWidth - $legendPadding;
        $legendY = ($this->canvasHeight - $legendOffset) / 2 + $legendOffset - $legendHeight / 2;

        $labelSettings = new ImagickDraw;

        $labelSettings->setFont($this->legendFont);
        $labelSettings->setFillColor($this->textColor->toImagickPixel());
        $labelSettings->setFontSize($legendFontSize);
        $labelSettings->setGravity(Imagick::GRAVITY_NORTHWEST);

        $i = 0;
        foreach ($this->slices as $sliceName => $slice) {
            // Move down...
            $OffsetY = $i++ * ($squareSize + $squarePadding);

            $keyPosX = $legendX;
            $keyPosY = $legendY + $OffsetY;

            // 1. Draw the key's colour square.
            $keySquare = new ImagickDraw;
            $keySquare->setFillColor($slice['color']->toImagickPixel());
            $keySquare->rectangle(
                $keyPosX,
                $keyPosY,
                $keyPosX + $squareSize,
                $keyPosY + $squareSize
            );

            $this->canvas->drawImage($keySquare);

            // 2. Draw the key's label.
            $labelMetrics = $this->canvas->queryFontMetrics($labelSettings, $sliceName);

            $this->canvas->annotateImage(
                $labelSettings,
                $keyPosX + $squareSize + $squarePadding,
                $keyPosY + $labelMetrics['descender'] / 2,
                0,
                $sliceName
            );
        }

        return $legendWidth + $legendPadding;
    }

    /**
     * @throws ImagickException
     * @throws ImagickDrawException
     */
    protected function _drawSlice($center, $radius, $start, $end, $color)
    {
        // Fine tuning, for a smoother edge.
        $radius -= 1;
        $center['x'] -= 1;
        $center['y'] -= 1;

        // 1. Drawn the curved part.
        $arc = new ImagickDraw;
        $color = $color->toImagickPixel();

        $arc->setFillColor($color);
        $arc->setStrokeColor($color);
        $arc->setStrokeWidth(1);

        $arc->arc(
            $center['x'] - $radius,
            $center['y'] - $radius,
            $center['x'] + $radius,
            $center['y'] + $radius,
            $start,
            $end
        );

        $this->canvas->drawImage($arc);

        // 2. Draw the triangular part.
        $startRadians = deg2rad($start);
        $endRadians = deg2rad($end);

        // Calculate position for start of slice.
        $startPosition = array(
            'x' => $center['x'] + $radius * cos($startRadians),
            'y' => $center['y'] + $radius * sin($startRadians)
        );

        // Calculate position for end of slice.
        $endPosition = array(
            'x' => $center['x'] + $radius * cos($endRadians),
            'y' => $center['y'] + $radius * sin($endRadians)
        );

        $trianglePoints = array($startPosition, $endPosition, $center);

        $triangle = new ImagickDraw;

        $triangle->setFillColor($color);
        $triangle->setStrokeColor($color);
        $triangle->setStrokeWidth(1);

        $triangle->polygon($trianglePoints);

        $this->canvas->drawImage($triangle);
    }

    /**
     * Returns the width, in pixels, of the chart's widest label.
     * @param float $fontSize
     * @return int
     * @throws ImagickException
     */
    protected function _maxLabelWidth(float $fontSize): int
    {
        $labelSettings = new ImagickDraw;
        $labelSettings->setFontSize($fontSize);
        $widestLabelWidth = 0;

        foreach ($this->slices as $sliceName => $slice) {
            // Measure the label.
            $labelMetrics = $this->canvas->queryFontMetrics($labelSettings, $sliceName);
            $labelWidth = $labelMetrics['textWidth'];

            if ($labelWidth > $widestLabelWidth)
                $widestLabelWidth = $labelWidth;
        }

        return $widestLabelWidth;
    }

    /**
     * Draws and returns the height of the title and its padding (in pixels). If no title is
     * specified, then nothing is drawn and 0 is returned.
     * @return int The height of the title + padding.
     * @throws ImagickException
     * @throws ImagickDrawException
     * @throws ImagickPixelException
     */
    protected function _drawTitle(): int
    {
        if (!$this->title) return 0;

        $titleSettings = new ImagickDraw;

        $titleSettings->setFont($this->titleFont);
        $titleSettings->setFillColor($this->textColor->toImagickPixel());
        $titleSettings->setGravity(Imagick::GRAVITY_NORTH);

        // Determine ideal font size for the title.
        $titleSize = 0.08 * $this->canvasHeight;    // The largest sensible value.
        $minTitleSize = 10;                   // The smallest legible value.

        do {
            $titleSettings->setFontSize($titleSize);

            // Measure the text.
            $titleBBox = $this->canvas->queryFontMetrics($titleSettings, $this->title);
            $titleWidth = $titleBBox['textWidth'];

            // If we can fit the title in, with 5% padding on each side, then we can draw it.
            if ($titleWidth <= ($this->canvasWidth * 0.9))
                break;

            $titleSize -= 0.5; // Try a smaller font size.
        } while ($titleSize >= $minTitleSize);

        $titleHeight = $titleBBox['textHeight'];

        // If the title is simply too long to be drawn legibly, then we will simply not draw it.
        if ($titleSize < $minTitleSize) return 0;

        // Give the title 7.5% top padding.
        $titleTopPadding = 0.075 * $this->canvasHeight;

        // Draw the title (centre-top).
        $this->canvas->annotateImage($titleSettings, 0, $titleTopPadding, 0, $this->title);

        return $titleHeight + $titleTopPadding;
    }
}
