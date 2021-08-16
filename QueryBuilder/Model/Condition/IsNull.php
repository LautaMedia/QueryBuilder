<?php
declare(strict_types=1);

namespace Query\Model\Condition;

use Query\Contract\Bindable;
use Query\Model\BindStorage;

class IsNull implements Bindable
{
    private string $condition;
    private BindStorage $binds;

    /**
     * IsNull constructor.
     * @param string|Bindable $column
     */
    public function __construct($column)
    {
        $this->binds = new BindStorage();
        $column = $this->binds->merge($column);
        $this->condition = sprintf('%s IS NULL', $column);

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