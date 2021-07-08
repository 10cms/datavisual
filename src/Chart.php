<?php

namespace cms10\DataVisual;

use cms10\DataVisual\Core\Color;

/**
 * Class Chart
 * @package cms10\DataVisual
 */
abstract class Chart extends Graph implements ChartInterface
{
    protected array $slices;

    protected string $title;

    protected bool $hasLegend;

    protected string $titleFont;

    protected string $legendFont;

    protected Color $textColor;

    /**
     * Constructs the Chart.
     * @param int $width The width of the chart, in pixels.
     * @param int $height The chart's height, in pixels.
     * @param array $options
     */
    public function __construct(int $width = 100, int $height = 100, array $options = [])
    {
        parent::__construct($width, $height, $options);

        $this->title = $options['title'] ?? '';
        $this->hasLegend = true;
        $this->textColor = new Color($options['textColor'] ?? 0x222222);

        $this->titleFont = __DIR__ . '/fonts/Open_Sans/OpenSans-Semibold.ttf';
        $this->legendFont = __DIR__ . '/fonts/Open_Sans/OpenSans-Regular.ttf';
    }

    /**
     * Sets the title's text. To remove the title, set it to ''.
     * @param string $title
     * @param string|null $titleFont
     * @return ChartInterface
     */
    public function setTitle(string $title, string $titleFont = NULL): ChartInterface
    {
        $this->title = $title;

        if ($titleFont)
            $this->titleFont = $titleFont;

        return $this;
    }

    /**
     * Add or remove the chart's legend (it is displayed default).
     * @param bool $displayLegend Specify false to remove the legend or true to
     * add one.
     * @param string|null [$legendFont] The name of the font for the legend's text.
     */
    public function setLegend(bool $displayLegend, string $legendFont = NULL): ChartInterface
    {
        $this->hasLegend = $displayLegend;

        if ($legendFont)
            $this->legendFont = $legendFont;

        return $this;
    }

    public function setSlices(array $slices): ChartInterface
    {
        $this->slices = $slices;

        return $this;
    }

    /**
     * Adds a new slice to the chart.
     * @param string $name The name of the slice (used for legend label).
     * @param float $value
     * @param string|int|array $color The CSS colour, e.g. '#FFFFFF', 'rgb(255, 255, 255)'.
     * @return ChartInterface
     */
    public function addSlice(string $name, float $value, $color): ChartInterface
    {
        $this->slices[$name] = array(
            'value' => $value,
            'color' => new Color($color)
        );
        return $this;
    }

    /**
     * Removes the specified slice.
     * @param string $name The name of the slice to be removed.
     */
    public function removeSlice(string $name): ChartInterface
    {
        unset($this->slices[$name]);

        return $this;
    }

}
