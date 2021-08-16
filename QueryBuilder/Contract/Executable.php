<?php
declare(strict_types=1);

namespace Query\Contract;



interface Executable extends SqlString, Debugable
{
    /** Execute the query
     * @param Executor $dbConnection
     */
    public function execute(Executor $dbConnection): void;
}