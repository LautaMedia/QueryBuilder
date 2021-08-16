<?php
declare(strict_types=1);

namespace Query\Contract;

interface Table extends FixedTable, Joinable, Selectable
{
    public function join(string $table, string $on, string $as = ''): Table;

    public function leftJoin(string $table, string $on, string $as = ''): Table;

    public function rightJoin(string $table, string $on, string $as = ''): Table;
}