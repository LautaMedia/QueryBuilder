<?php
declare(strict_types=1);

namespace Query\Model\Query;


use PDO;
use Query\Contract\Bindable;
use Query\Contract\Executor;
use Query\Contract\FilteredTable;
use Query\Contract\Table;
use Query\Contract\Tableable;
use Query\Contract\Unionable;
use Query\Contract\WithSelectable;
use Query\Model\BindStorage;

class With implements \Query\Contract\With, WithSelectable, Tableable, Table {
    private array $ctes = [];
    /**
     * @var Select
     */
    private Select $query;
    private string $queryString = '';
    private bool $string = false;
    private bool $recursive;
    private BindStorage $binds;

    public function __construct(bool $recursive = false)
    {
        $this->query = new Select();
        $this->recursive = $recursive;
        $this->binds = new BindStorage();
    }

    public function __toString(): string
    {
        $clone = clone $this;

        if (!$clone->string) {
            $clone->queryString = $clone->binds->merge($clone->query);
        }

        $cmd = ($this->recursive ? 'WITH RECURSIVE' : 'WITH');
        $cteStrings = array_map(function($cte) {
            if (empty($cte[2])) {
                return sprintf('%s AS (%s)', $cte[0], (string) $cte[1]);
            }
            return sprintf('%s (%s) AS (%s)', $cte[0], implode(', ', $cte[2]), $cte[1]);
        }, $this->ctes);

        return sprintf('%s %s %s', $cmd, implode(", ", $cteStrings), (string) $clone->queryString);
    }

    public function bind(string $key, $value): With
    {
        $clone = clone $this;

        $ctes = [];
        foreach ($this->ctes as $cte) {
            $ctes[] = $this->binds->bind($cte, $key, $value);
        }
        $this->ctes = $ctes;

        if (!$this->string) {
            $this->query->bind($key, $value);
        }

        return $clone;
    }

    public function get(Executor $dbConnection): \Query\Contract\Result
    {
        $clone = clone $this;

        $query = $dbConnection->prepare($clone->queryString);
        foreach ($clone->binds->binds() as $key => $value) {
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

    public function union(Unionable $executable): FilteredTable
    {
        return new Union($this, $executable);
    }

    public function unionAll(Unionable $executable): FilteredTable
    {
        return new Union($this, $executable, true);
    }

    public function as(string $alias): Bindable
    {
        return new Alias($this, $alias);
    }

    public function cte(string $name, Unionable $subQuery, array $columns = []): With
    {
        $clone = clone $this;
        $clone->ctes[] = [$name , $clone->binds->merge($subQuery), $columns];
        return $clone;
    }

    public function select(...$fields): With
    {
        $clone = clone $this;
        $clone->query = $this->query->select(...$fields);
        return $clone;

    }

    public function from($table, string $alias = ''): With
    {
        $clone = clone $this;
        $clone->query = $this->query->from($table, $alias);
        return $clone;
    }

    public function limit(int $limit, int $offset = 0): With
    {
        $clone = clone $this;
        $clone->query = $this->query->limit($limit, $offset);
        return $clone;
    }

    public function asc(string $column): With
    {
        $clone = clone $this;
        $clone->query = $this->query->asc($column);
        return $clone;
    }

    public function desc(string $column): With
    {
        $clone = clone $this;
        $clone->query = $this->query->desc($column);
        return $clone;
    }

    public function where($condition): With
    {
        $clone = clone $this;
        $clone->query = $this->query->where($condition);
        return $clone;
    }

    public function group(string $column): With
    {
        $clone = clone $this;
        $clone->query = $this->query->group($column);
        return $clone;
    }

    public function having($condition): With
    {
        $clone = clone $this;
        $clone->query = $this->query->having($condition);
        return $clone;
    }

    public function join(string $table, string $on, string $as = ''): With
    {
        $clone = clone $this;
        $clone->query = $this->query->join($table, $on, $as);
        return $clone;
    }

    public function leftJoin(string $table, string $on, string $as = ''): With
    {
        $clone = clone $this;
        $clone->query = $this->query->leftJoin($table, $on, $as);
        return $clone;
    }

    public function rightJoin(string $table, string $on, string $as = ''): With
    {
        $clone = clone $this;
        $clone->query = $this->query->rightJoin($table, $on, $as);
        return $clone;
    }

    /**
     * @psalm-internal This function is only for development
     * @return static
     */
    public function debug()
    {
        $clone = clone $this;

        if (!$this->string){
            $clone->queryString = $clone->binds->merge($clone->query);
            $clone->string = true;
        }

        echo (string) $clone . "\n";
        foreach ($clone->binds->binds() as $key => $value) {
            echo sprintf("%s => %s (%s)\n", $key, $value, gettype($value));
        }

        return $this;
    }

    public function table($name): With
    {
        $clone = clone $this;
        $clone->query = $this->query->from($name);
        return $clone;
    }

    public function query(Unionable $select): Unionable
    {
        $clone = clone $this;
        $clone->queryString = $clone->binds->merge($select);
        $clone->string = true;
        return $clone;
    }

    public function binds(): array
    {
        return $this->binds->binds();
    }
}