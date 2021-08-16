<?php
declare(strict_types=1);

namespace Query\Model\Condition;


use Query\Contract\Bindable;
use Query\Model\BindStorage;

class In implements Bindable
{

    private string $condition;
    private BindStorage $binds;

    /**
     * In constructor.
     * @param string|Bindable $key
     * @param array|Bindable $set
     */
    public function __construct($key, $set)
    {
        $this->binds = new BindStorage();
        $key = $this->binds->merge($key);
        if (is_array($set)){
            $set = $this->binds->mergeMultiple($set);
            $this->condition = sprintf('%s IN (%s)', $key, implode(', ', $set));
        }
        else {
            $set = $this->binds->merge($set);
            $this->condition = sprintf('%s IN (%s)', $key, $set);
        }
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