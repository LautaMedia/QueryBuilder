<?php
declare(strict_types=1);

namespace Query\Model\Condition;

use Query\Contract\Bindable;
use Query\Model\BindStorage;

class Equal implements Bindable
{
    private string $condition;
    private BindStorage $binds;

    /**
     * Equal constructor.
     * @param string|Bindable $a
     * @param string|Bindable $b
     */
    public function __construct($a, $b)
    {
        $this->binds = new BindStorage();
        $a = $this->binds->merge($a);
        $b = $this->binds->merge($b);
        $this->condition = sprintf('%s = %s', $a, $b);
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