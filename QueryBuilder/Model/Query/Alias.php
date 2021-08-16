<?php
declare(strict_types=1);


namespace Query\Model\Query;



use Query\Contract\Bindable;
use Query\Contract\Debugable;
use Query\Contract\SqlString;
use Query\Contract\Unionable;

class Alias implements SqlString, Bindable, Debugable
{
    private Unionable $unionable;
    private string $alias;

    public function __construct(Unionable $unionable, string $alias) {
        $this->unionable = $unionable;
        $this->alias = $alias;
    }

    public function __toString(): string
    {
        return sprintf('(%s) AS %s', (string) $this->unionable, $this->alias);
    }

    public function bind(string $key, $value): Bindable
    {
        $clone = clone $this;
        $clone->unionable->bind($key, $value);
        return $clone;
    }

    public function binds(): array
    {
        return $this->unionable->binds();
    }

    /**
     * @psalm-internal This function is only for development
     * @return static
     */
    public function debug()
    {
        echo (string) $this . "\n";
        foreach ($this->unionable->binds() as $key => $value) {
            echo sprintf("%s => %s (%s)\n", $key, $value, gettype($value));
        }

        return $this;
    }
}