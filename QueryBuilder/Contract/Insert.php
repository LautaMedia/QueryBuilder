<?php
declare(strict_types=1);

namespace Query\Contract;

use PDO;

interface Insert extends Executable
{
    /** Set a value to be inserted into the a column
     * @param string $column
     * @param mixed $value Can safely be anything
     * @return Insert
     */
    public function value(string $column, $value, int $type = PDO::PARAM_STR): Insert;
}