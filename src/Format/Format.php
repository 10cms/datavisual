<?php
namespace cms10\DataVisual\Format;

use cms10\DataVisual\Graph;
use ImagickException;

abstract class Format
{
    protected Graph $graph;

    protected string $type = '';

    /**
     * Format constructor.
     * @param Graph $graph
     */
    public function __construct(Graph $graph)
    {
        $this->graph = $graph;
    }

    /**
     * @throws ImagickException
     */
    public function response()
    {
        header("Content-Type: image/$this->type");
        echo $this->graph->canvas->getImageBlob();
    }

    /**
     * @throws ImagickException
     */
    public function save(string $filename, bool $force = false): bool
    {
        if ($force) {
            header("Pragma: public");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
        } else {
            header("Content-Type: image/$this->type");
            header("Content-Disposition: inline; filename=\"$filename\"");
        }

        return $this->graph->canvas->writeImage($filename);
    }
}