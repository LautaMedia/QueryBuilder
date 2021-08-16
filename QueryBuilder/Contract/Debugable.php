<?php
declare(strict_types=1);


namespace Query\Contract;

interface Debugable
{
    /**
     * @return static
     */
    public function debug();
}