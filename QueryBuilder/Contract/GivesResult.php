<?php
declare(strict_types=1);

namespace Query\Contract;

interface GivesResult extends Bindable, Executable
{

    /** Execute the query and return the result
     * @param Executor $dbConnection
     * @return Result
     */
    public function get(Executor $dbConnection): Result;
}