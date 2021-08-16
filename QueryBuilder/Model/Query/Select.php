<?php
declare(strict_types=1);

namespace Query\Model\Query;


use PDO;
use Query\Contract\Executor;
use Query\Contract\Result;
use Query\Contract\Selectable;
use Query\Contract\Table;
use Query\Contract\Tableable;
use Query\Contract\Unionable;
use Query\Model\BindStorage;
use function Query\and_;

class Select implements Selectable, Table, Tableable
{
    private array $fields = [];
    private string $from = '';
    private string $alias = '';
    private string $condition = '';
    private string $having = '';
    private BindStorage $binds;
    private int $limit = 0;
    private int $offset = 0;
    private array $order = [];
    private array $group = [];
    private array $join = [];

    public function __construct() {
        $this->binds = new BindStorage();
    }

    /**
     * @param string|Unionable ...$fields
     * @return Select
     */
    public function select(...$fields): Select
    {
        $clone = clone $this;

        foreach ($fields as $field) {
            $clone->fields[] = $clone->binds->merge($field);
        }

        return $clone;
    }

    public function from($table, string $alias = ''): Select
    {
        $clone = clone $this;
        $clone->from = $clone->binds->merge($table);
        $clone->alias = $alias;
        return $clone;
    }

    public function where($condition): Select
    {
        $clone = clone $this;
        $condition = $clone->binds->merge($condition);
        if (!$clone->condition) {
            $clone->condition = $condition;
        } else {
            $clone->condition = (string) and_($clone->condition, $condition);
        }

        return $clone;
    }

    public function execute(Executor $dbConnection): void
    {
        $this->get($dbConnection);
    }


    public function bind(string $key, $value): Select
    {
        $clone = clone $this;

        $clone->from = $clone->binds->bind($clone->from, $key, $value);
        $clone->condition = $clone->binds->bind($clone->condition, $key, $value);
        $clone->having = $clone->binds->bind($clone->having, $key, $value);

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
        if (empty($this->fields)) {
            $fields = ['*'];
        } else {
            $fields = $this->fields;
        }
        $query = 'SELECT ' . implode(', ', $fields);
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
        if ($this->group) {
            $query .= ' GROUP BY ' . implode(', ', $this->group);
        }
        if ($this->having) {
            $query .= sprintf(" HAVING %s", $this->having);
        }
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

    public function as(string $alias): Alias
    {
        return new Alias($this, $alias);
    }

    public function union(Unionable $executable): Union
    {
        return new Union($this, $executable);
    }

    public function unionAll(Unionable $executable): Union
    {
        return new Union($this, $executable, true);
    }

    public function limit(int $limit, int $offset = 0): Select
    {
        $clone = clone $this;
        $clone->limit = $limit;
        $clone->offset = $offset;

        return $clone;
    }

    public function asc(string $column): Select
    {
        $clone = clone $this;
        $clone->order[$column] = true;

        return $clone;
    }

    public function desc(string $column): Select
    {
        $clone = clone $this;
        $clone->order[$column] = false;

        return $clone;
    }

    public function group(string $column): Select
    {
        $clone = clone $this;
        $clone->group[] = $column;

        return $clone;
    }

    public function having($condition): Select
    {
        $clone = clone $this;
        $condition = $clone->binds->merge($condition);
        if (!$clone->having) {
            $clone->having = $condition;
        } else {
            $clone->having = (string) and_($clone->having, $condition);
        }

        return $clone;
    }

    public function join(string $table, string $on, string $as = ''): Select
    {
        $clone = clone $this;
        $clone->join[] = ['INNER', $table, $on, $as];

        return $clone;
    }

    public function leftJoin(string $table, string $on, string $as = ''): Select
    {
        $clone = clone $this;
        $clone->join[] = ['LEFT', $table, $on, $as];

        return $clone;
    }

    public function rightJoin(string $table, string $on, string $as = ''): Select
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