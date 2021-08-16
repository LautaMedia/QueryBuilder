<?php
declare(strict_types=1);

namespace Query\Model\Query;


use PDO;
use Query\Contract\Executor;
use Query\Contract\FilteredTable;
use Query\Contract\Joinable;
use Query\Contract\Result;
use Query\Contract\Unionable;
use Query\Model\BindStorage;

class Delete implements Unionable, Joinable
{
    private string $from = '';
    private string $alias = '';
    private string $condition = '';
    private BindStorage $binds;
    private array $order = [];
    private array $join = [];

    public function __construct()
    {
        $this->binds = new BindStorage();
    }

    public function from(string $from, string $alias = ''): Delete
    {
        $clone = clone $this;
        $clone->from = $from;
        $clone->alias = $alias;

        return $clone;
    }

    public function where($condition): Delete
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

    public function bind(string $key, $value): Delete
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
        $query = 'DELETE';
        if ($this->from) {
            if (empty($this->alias)) {
                $query .= ' FROM ' . $this->from;
            } else {
                $query .= ' FROM ' . implode(' ', [$this->from, $this->alias]);
            }
            if ($this->join) {
                foreach ($this->join as $join) {
                    if (empty($join[3])) {
                        $table = $join[1];
                    } else {
                        $table = implode(' ', [$join[1], $join[3]]);
                    }
                    $query .= sprintf(' %s JOIN %s ON %s', $join[0], $table, $join[2]);
                }
            }
        }
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

    public function as(string $alias): Alias
    {
        return new Alias($this, $alias);
    }

    public function union(Unionable $executable): FilteredTable
    {
        return new Union($this, $executable);
    }

    public function unionAll(Unionable $executable): FilteredTable
    {
        return new Union($this, $executable, true);
    }

    public function asc(string $column): Delete
    {
        $clone = clone $this;
        $clone->order[$column] = true;

        return $clone;
    }

    public function desc(string $column): Delete
    {
        $clone = clone $this;
        $clone->order[$column] = false;

        return $clone;
    }

    public function join(string $table, string $on, string $as = ''): Delete
    {
        $clone = clone $this;
        $clone->join[] = ['INNER', $table, $on, $as];

        return $clone;
    }

    public function leftJoin(string $table, string $on, string $as = ''): Delete
    {
        $clone = clone $this;
        $clone->join[] = ['LEFT', $table, $on, $as];

        return $clone;
    }

    public function rightJoin(string $table, string $on, string $as = ''): Delete
    {
        $clone = clone $this;
        $clone->join[] = ['RIGHT', $table, $on, $as];

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