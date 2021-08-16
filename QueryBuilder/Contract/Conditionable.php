<?php
declare(strict_types=1);

namespace Query\Contract;

interface Conditionable extends Bindable, Executable
{
    /** Add conditions to the query
     * @param string|Bindable $condition
     * @return Conditionable
     */
    public function where($condition): Conditionable;
}