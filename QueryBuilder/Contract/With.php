<?php
declare(strict_types=1);


namespace Query\Contract;


interface With
{
    public function cte(string $name, Unionable $subQuery, array $columns = []): WithSelectable;
}