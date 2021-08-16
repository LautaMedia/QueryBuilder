<?php
declare(strict_types=1);

namespace Query\Model\Condition;

use Query\Contract\Bindable;
use Query\Model\BindStorage;

class All implements Bindable
{
    private string $condition;
    private BindStorage $binds;

    /**
     * All constructor.
     * @param string|Bindable ...$conditions
     */
    public function __construct(...$conditions)
    {
        $this->binds = new BindStorage();
        $conditions = $this->binds->mergeMultiple($conditions);
        $this->condition = sprintf('(%s)', implode(') AND (', $conditions));

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