<?php

namespace cms10\DataVisual\Core;


/**
 * Utility class for storing box model.
 */
class Box
{
    public int $top;
    public int $right;
    public int $bottom;
    public int $left;

    /**
     * Sets the colour using {@link setBox()}, if an argument is provided.
     * @param array|string [$box]
     */
    public function __construct($box = NULL)
    {
        if (!is_null($box)) {
            $this->setBox($box);
        }
    }

    /**
     * Sets the box model, using one of the following formats: '5 10 10 5' like css,
     * [5, 10, 10, 5]
     * @param array|string $box
     */
    public function setBox($box)
    {
        switch (getType($box)) {
            case 'array':
                $count = count($box);
                if ($count == 4) {
                    $this->top = $box[0];
                    $this->right = $box[1];
                    $this->bottom = $box[2];
                    $this->left = $box[3];
                } elseif ($count == 3) {
                    $this->top = $box[0];
                    $this->right = $box[1];
                    $this->bottom = $box[2];
                    $this->left = $box[1];
                } elseif ($count == 2) {
                    $this->top = $box[0];
                    $this->right = $box[1];
                    $this->bottom = $box[0];
                    $this->left = $box[1];
                } elseif ($count == 1) {
                    $this->top = $box[0];
                    $this->right = $box[0];
                    $this->bottom = $box[0];
                    $this->left = $box[0];
                }
                break;

            case 'string':
                $box = explode(' ', $box);
                $this->setBox($box);
                break;
        }
    }

}