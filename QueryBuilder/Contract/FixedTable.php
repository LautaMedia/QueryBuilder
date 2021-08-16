<?php
declare(strict_types=1);

namespace Query\Contract;

/** FixedTable has a fixed amount of columns */
interface FixedTable extends GroupedTable, Conditionable
{
    public function where($condition): FixedTable;
}