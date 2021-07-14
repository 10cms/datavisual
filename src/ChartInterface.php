<?php


namespace cms10\DataVisual;


interface ChartInterface
{
    public function setTitle(string $title, $titleFont = NULL): ChartInterface;

    public function setLegend(bool $displayLegend, $legendFont = NULL): ChartInterface;

    public function setSlices(array $slices): ChartInterface;

    public function addSlice(string $name, float $value, $color): ChartInterface;

    public function removeSlice(string $name): ChartInterface;
}