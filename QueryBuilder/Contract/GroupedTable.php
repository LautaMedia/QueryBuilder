<?php
declare(strict_types=1);

namespace Query\Contract;

interface GroupedTable extends GivesResult, HavingTable
{
    public function group(string $column): GroupedTable;
}