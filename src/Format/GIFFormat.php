<?php
namespace cms10\DataVisual\Format;

use cms10\DataVisual\Graph;
use Imagick;
use ImagickException;

class GIFFormat extends Format
{
    /**
     * @throws ImagickException
     */
    public function __construct(Graph $graph)
    {
        parent::__construct($graph);

        $this->type = 'gif';

        $this->graph->canvas->setImageFormat('gif');
    }
}