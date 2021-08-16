<?php
declare(strict_types=1);


namespace Query\Contract;

interface WithSelectable extends With
{
    /**
     * @param string|Bindable $name
     * @return Table
     */
    public function table($name): Table;

    /**
     * @param string|Unionable ...$fields
     * @return Tableable
     */
    public function select(...$fields): Tableable;

    public function query(Unionable $select): Unionable;
}