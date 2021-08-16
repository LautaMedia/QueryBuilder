<?php
declare(strict_types=1);

namespace Query\Contract;

interface Unionable extends GivesResult
{
    /** Return the query as an aliased sub query which can be given as a parameter to \Query\select() function
     * @param string $alias
     * @return Bindable
     */
    public function as(string $alias): Bindable;

    /** Union this query to another query
     * @param Unionable $executable
     * @return FilteredTable
     */
    public function union(Unionable $executable): FilteredTable;

    /** Union this query to another query but keep duplicates
     * @param Unionable $executable
     * @return FilteredTable
     */
    public function unionAll(Unionable $executable): FilteredTable;
}