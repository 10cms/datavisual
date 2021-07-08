<?php


namespace cms10\DataVisual;


use cms10\DataVisual\Core\Color;
use Imagick;

abstract class Graph
{
    protected int $width;

    protected int $height;

    protected Color $backcolor;

    public Imagick $canvas;

    public int $quality;

    public function __construct(int $width = 100, int $height = 100, array $options = [])
    {
        $this->width = $width;
        $this->height = $height;
        $this->quality = 100;
        $this->backcolor = new Color($options['backcolor'] ?? 0xffffff);

        $this->canvas = new Imagick();
    }

    public function __destruct()
    {
        $this->canvas->destroy();
    }

    /**
     * Set the quality for generating output in lossy formats.
     * @param int $quality An integer between 0 and 100 (inclusive).
     */
    public function setQuality(int $quality)
    {
        $this->quality = $quality;
    }

    /**
     * Draws the Graph so that it is ready for output.
     */
    abstract public function draw();
}