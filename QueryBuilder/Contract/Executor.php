<?php
declare(strict_types=1);


namespace Query\Contract;


interface Executor
{
    public function prepare(string $param): \PDOStatement;
}