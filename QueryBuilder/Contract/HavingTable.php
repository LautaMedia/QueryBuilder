<?php
declare(strict_types=1);

namespace Query\Contract;

interface HavingTable extends FilteredTable
{
    /** Add conditions to the query which will be applied later than where conditions
     * @param string|Bindable $condition
     * @return HavingTable
     */
    public function having($condition): HavingTable;
}