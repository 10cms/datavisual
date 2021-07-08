<?php
namespace cms10\DataVisual\Format;

use cms10\DataVisual\Graph;
use Imagick;
use ImagickException;

class PNGFormat extends Format
{
    /**
     * @throws ImagickException
     */
    public function __construct(Graph $graph)
    {
        parent::__construct($graph);

        $this->type = 'png';

        $this->graph->canvas->setImageFormat('png');
    }
}