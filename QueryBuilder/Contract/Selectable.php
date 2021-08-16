<?php
declare(strict_types=1);

namespace Query\Contract;

interface Selectable
{
    /**
     * @param string|Unionable ...$fields
     * @return Table
     */
    public function select(...$fields): Table;
}