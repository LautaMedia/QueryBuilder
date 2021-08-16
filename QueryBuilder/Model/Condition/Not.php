<?php
declare(strict_types=1);

namespace Query\Model\Condition;


use Query\Contract\Bindable;
use Query\Model\BindStorage;

class Not implements Bindable
{

    private string $condition;
    private BindStorage $binds;

    /**
     * Not constructor.
     * @param string|Bindable $a
     */
    public function __construct($a)
    {
        $this->binds = new BindStorage();
        $a = $this->binds->merge($a);
        $this->condition = sprintf('NOT %s', $a);
    }


    public function __toString(): string
    {
        return $this->condition;
    }

    public function bind(string $key, $value): Bindable
    {
        $clone = clone $this;
        $clone->condition = $clone->binds->bind($this->condition, $key, $value);
        return $clone;
    }

    public function binds(): array
    {
        return $this->binds->binds();
    }
}