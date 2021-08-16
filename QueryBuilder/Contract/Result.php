<?php
declare(strict_types=1);

namespace Query\Contract;

use stdClass;

interface Result
{
    /** Get result rows
     * @return stdClass[] List of rows
     */
    public function rows(): array;
}