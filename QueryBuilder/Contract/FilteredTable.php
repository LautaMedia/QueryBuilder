<?php
declare(strict_types=1);

namespace Query\Contract;

/** FilteredTable can only be sorted or limited*/
interface FilteredTable extends Unionable
{
    /** Limit query results
     * @param int $limit
     * @param int $offset
     * @return Unionable
     */
    public function limit(int $limit, int $offset = 0): Unionable;

    /** Sort a column in ascending order
     * @param string $column
     * @return FilteredTable
     */
    public function asc(string $column): FilteredTable;

    /** Sort a column in descending order
     * @param string $column
     * @return FilteredTable
     */
    public function desc(string $column): FilteredTable;
}