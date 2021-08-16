<?php
declare(strict_types=1);

namespace Query\Model\Query;


use PDO;
use Query\Contract\Executor;
use Query\Contract\Result;
use Query\Contract\Set;
use Query\Contract\Settable;
use Query\Model\BindStorage;

class Update implements Settable, Set
{
    private string $table;
    private array $sets = [];
    private string $condition = '';
    private BindStorage $binds;
    private array $order = [];

    public function __construct(string $table)
    {
        $this->table = $table;
        $this->binds = new BindStorage();
    }

    public function where($condition): Update
    {
        $clone = clone $this;
        $condition = $clone->binds->merge($condition);
        if (!$clone->condition) {
            $clone->condition = $condition;
        } else {
            $clone->condition = (string) \Query\and_($clone->condition, $condition);
        }

        return $clone;
    }

    public function execute(Executor $dbConnection): void
    {
        $this->get($dbConnection);
    }

    public function bind(string $key, $value): Update
    {
        $clone = clone $this;
        $clone->condition = $clone->binds->bind($clone->condition, $key, $value);
        return $clone;
    }

    public function get(Executor $dbConnection): Result
    {
        $query = $dbConnection->prepare((string)$this);
        foreach ($this->binds->binds() as $key => $value) {
            $type = PDO::PARAM_STR;
            if (is_bool($value)) {
                $type = PDO::PARAM_BOOL;
            }
            $query->bindValue($key, $value, $type);
        }

        return new \Query\Model\Query\Result($query);
    }

    public function __toString(): string
    {
        $query = '';
        if ($this->table) {
            $query .= 'UPDATE ' . $this->table . ' SET ';
        }
        $parts = [];
        foreach ($this->sets as $column => $value) {
            $parts[] = sprintf('%s = %s', $column, $value);
        }
        $query .= implode(', ', $parts);
        if ($this->condition) {
            $query .= ' WHERE ' . $this->condition;
        }
        if ($this->order) {
            $orders = [];
            foreach ($this->order as $column => $asc) {
                $orders[] = sprintf('%s %s', $column, $asc ? 'ASC' : 'DESC');
            }
            $orderString = implode(', ', $orders);
            $query .= sprintf(' ORDER BY %s', $orderString);
        }

        return $query;
    }

    public function asc(string $column): Update
    {
        $clone = clone $this;
        $clone->order[$column] = true;

        return $clone;
    }

    public function desc(string $column): Update
    {
        $clone = clone $this;
        $clone->order[$column] = false;

        return $clone;
    }

    public function set(string $column, $value): Update
    {
        $clone = clone $this;
        $parameter = ':_' . $column;
        $clone->sets[$column] = $clone->binds->bind($parameter, $parameter, $value);

        return $clone;
    }

    public function setRaw(string $column, string $raw): Update
    {
        $clone = clone $this;
        $clone->sets[$column] = $raw;

        return $clone;
    }

    /**
     * @psalm-internal This function is only for development
     * @return static
     */
    public function debug()
    {
        echo (string) $this . "\n";
        foreach ($this->binds->binds() as $key => $value) {
            echo sprintf("%s => %s (%s)\n", $key, $value, gettype($value));
        }

        return $this;
    }

    public function binds(): array
    {
        return $this->binds->binds();
    }
}