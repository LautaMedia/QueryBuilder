<?php
declare(strict_types=1);

namespace Query\Contract;

interface Insert extends Executable
{
    /** Set a value to be inserted into the a column
     * @param string $column
     * @param mixed $value Can safely be anything
     * @param bool $onDuplicateUpdate Update value of this field on duplicate
     * @param string $to Update value of this field to this value, VALUES(x) by default
     * @return Insert
     */
    public function value(string $column, $value, bool $onDuplicateUpdate = false, string $to = ''): Insert;
}