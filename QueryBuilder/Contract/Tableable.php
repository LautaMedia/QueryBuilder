<?php
declare(strict_types=1);

namespace Query\Contract;

interface Tableable extends Selectable, Unionable
{
    /** Select from table
     * @param string|Bindable $table
     * @param string $alias
     * @return Table
     */
    public function from($table, string $alias = ''): Table;
}