<?php
namespace cms10\DataVisual\Format;

use cms10\DataVisual\Graph;
use Imagick;
use ImagickException;

class JPEGFormat extends Format
{
    /**
     * @throws ImagickException
     */
    public function __construct(Graph $graph)
    {
        parent::__construct($graph);

        $this->type = 'jpeg';

        $this->graph->canvas->setImageFormat('jpeg');
        $this->graph->canvas->setImageCompression(imagick::COMPRESSION_JPEG);
        $this->graph->canvas->setImageCompressionQuality($this->graph->quality);
    }
}