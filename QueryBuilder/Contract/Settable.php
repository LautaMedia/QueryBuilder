<?php
declare(strict_types=1);

namespace Query\Contract;

interface Settable extends SqlString
{
    /** Set column value
     * @param string $column
     * @param mixed $value Can safely be anything
     * @return Set
     */
    public function set(string $column, $value): Set;

    /** Set column value to a raw SQL value i. e. CURRENT_TIMESTAMP
     * @param string $column
     * @param string $raw
     * @return Set
     */
    public function setRaw(string $column, string $raw): Set;
}