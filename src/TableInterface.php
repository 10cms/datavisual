<?php


namespace cms10\DataVisual;


interface TableInterface
{
    public function setColumns(array $columns): TableInterface;

    public function setRows(array $rows): TableInterface;

    public function query(array $columns): array;

    public function addRow(array $row): TableInterface;

    public function removeRow(array $columns): TableInterface;
}