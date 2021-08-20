<?php
declare(strict_types=1);

namespace Query\Model\Query;


use PDO;
use Query\Contract\Executor;

class Insert implements \Query\Contract\Insert
{
    private string $table;
    private bool $ignore;
    private array $values = [];

    public function __construct(string $table, bool $ignore)
    {
        $this->table = $table;
        $this->ignore = $ignore;
    }

    public function __toString(): string
    {
        $cmd = ($this->ignore ? 'INSERT IGNORE INTO' : 'INSERT INTO');
        $fields = array_keys($this->values);
        $columns = implode(', ', $fields);
        $positionalParams = array_fill(0, count($fields), '?');
        $positionalParamString = sprintf('(%s)', implode(', ', $positionalParams));
        $count = $this->values ? count(max($this->values)) : 0;
        $params = implode(', ', array_fill(0, $count, $positionalParamString));
        return sprintf('%s %s(%s) VALUES %s', $cmd, $this->table, $columns, $params);
    }

    public function value(string $column, $value, int $type = PDO::PARAM_STR): Insert
    {
        if (is_bool($value)) {
            $type = PDO::PARAM_BOOL;
        }
        $clone = clone $this;
        if (array_key_exists($column, $clone->values)) {
            $clone->values[$column][] = [$value, $type];
        } else {
            $clone->values[$column] = [[$value, $type]];
        }

        return $clone;
    }

    public function execute(Executor $dbConnection): void
    {
        $query = $dbConnection->prepare((string)$this);
        $count = $this->values ? count(max($this->values)) : 0;
        $index = 1;
        for ($set = 0; $set < $count; $set++) {
            foreach ($this->values as $key => $value) {
                $query->bindValue($index, $value[$set][0], $value[$set][1]);
                ++$index;
            }
        }
        $query->execute();
    }

    /**
     * @psalm-internal This function is only for development
     * @return static
     */
    public function debug()
    {
        echo (string) $this . "\n";
        $count = $this->values ? count(max($this->values)) : 0;

        $index = 1;
        for ($set = 0; $set < $count; $set++) {
            echo sprintf("%s:", $index);
            foreach ($this->values as $key => $value) {
                echo sprintf(' %s => %s (%s),', $key, $value[$set][0], gettype($value[$set][0]));
                ++$index;
            }
            echo "\n";
        }

        return $this;
    }
}