<?php
declare(strict_types=1);

namespace Query\Contract;

interface Joinable extends Conditionable
{
    /** Join a table to the selection on specific conditions
     * @param string $table
     * @param string $on Conditions
     * @param string $as Alias
     * @return Joinable
     */
    public function join(string $table, string $on, string $as = ''): Joinable;

    /** Join a table to the selection on specific conditions
     * @param string $table
     * @param string $on Conditions
     * @param string $as Alias
     * @return Joinable
     */
    public function leftJoin(string $table, string $on, string $as = ''): Joinable;

    /** Join a table to the selection on specific conditions
     * @param string $table
     * @param string $on Conditions
     * @param string $as Alias
     * @return Joinable
     */
    public function rightJoin(string $table, string $on, string $as = ''): Joinable;
}