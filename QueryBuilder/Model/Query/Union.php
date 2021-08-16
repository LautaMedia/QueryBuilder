<?php
declare(strict_types=1);

namespace Query\Model\Query;

use PDO;
use Query\Contract\Executor;
use Query\Contract\FilteredTable;
use Query\Contract\Unionable;
use Query\Model\BindStorage;

class Union implements FilteredTable
{
    private string $query1;
    private string $query2;
    private bool $all;
    private string $having = '';
    private BindStorage $binds;
    private int $limit = 0;
    private int $offset = 0;
    private array $order = [];

    public function __construct(Unionable $query1, Unionable $query2, bool $all = false)
    {
        $this->binds = new BindStorage();
        $this->query1 = $this->binds->merge($query1);
        $this->query2 = $this->binds->merge($query2);

        $this->all = $all;
    }

    public function bind(string $key, $value): Union
    {
        $clone = clone $this;

        $clone->query1 = $clone->binds->bind($clone->query1, $key, $value);
        $clone->query2 = $clone->binds->bind($clone->query2, $key, $value);
        $clone->having = $clone->binds->bind($clone->having, $key, $value);

        return $clone;
    }

    public function get(Executor $dbConnection): \Query\Contract\Result
    {
        $query = $dbConnection->prepare((string)$this);
        foreach ($this->binds->binds() as $key => $value) {
            $type = PDO::PARAM_STR;
            if (is_bool($value)) {
                $type = PDO::PARAM_BOOL;
            }
            $query->bindValue($key, $value, $type);
        }

        return new Result($query);
    }

    public function execute(Executor $dbConnection): void
    {
        $this->get($dbConnection);
    }

    public function __toString(): string
    {
        if ($this->all) {
            $union = 'UNION ALL';
        } else {
            $union = 'UNION';
        }

        $query = implode(' ', ['(' . $this->query1 . ')', $union, '(' . $this->query2 . ')']);

        if ($this->order) {
            $orders = [];
            foreach ($this->order as $column => $asc) {
                $orders[] = sprintf('%s %s', $column, $asc ? 'ASC' : 'DESC');
            }
            $orderString = implode(', ', $orders);
            $query .= sprintf(' ORDER BY %s', $orderString);
        }

        if ($this->limit) {
            $query .= sprintf(' LIMIT %s, %s', $this->offset, $this->limit);
        }

        return $query;
    }

    public function union(Unionable $executable): Union
    {
        return new Union($this, $executable);
    }

    public function unionAll(Unionable $executable): Union
    {
        return new Union($this, $executable, true);
    }

    public function as(string $alias): Alias
    {
        return new Alias($this, $alias);
    }

    public function limit(int $limit, int $offset = 0): Union
    {
        $clone = clone $this;
        $clone->limit = $limit;
        $clone->offset = $offset;

        return $clone;
    }

    public function asc(string $column): Union
    {
        $clone = clone $this;
        $clone->order[$column] = true;

        return $clone;
    }

    public function desc(string $column): Union
    {
        $clone = clone $this;
        $clone->order[$column] = false;

        return $clone;
    }

    public function having(string $condition): Union
    {
        $clone = clone $this;
        if (!$clone->having) {
            $clone->having = $condition;
        } else {
            $clone->having = (string) \Query\and_($clone->having, $condition);
        }

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