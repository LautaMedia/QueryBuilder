<?php
declare(strict_types=1);

namespace Query\Contract;

/** String that contains an sql expression or query. Primarily used for testing. */
interface SqlString
{
    public function __toString(): string;
}