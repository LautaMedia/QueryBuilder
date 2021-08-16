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
    private array $duplicateRules = [];

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
        if ($this->duplicateRules){
            $rules = [];
            foreach($this->duplicateRules as $column => $rule){
                $rules[] = sprintf('%s = %s', $column, $rule);
            }
            $ruleString = implode(', ', $rules);
            $params = sprintf('%s ON DUPLICATE KEY UPDATE %s', $params, $ruleString);
        }
        return sprintf('%s %s(%s) VALUES %s', $cmd, $this->table, $columns, $params);
    }

    public function value(string $column, $value, bool $onDuplicateUpdate = false, string $to = ''): Insert
    {
        $clone = clone $this;
        if (array_key_exists($column, $clone->values)) {
            $clone->values[$column][] = $value;
        } else {
            $clone->values[$column] = [$value];
        }
        if ($onDuplicateUpdate){
            if ($to){
                $clone->duplicateRules[$column] = $to;
            } else {
                $clone->duplicateRules[$column] = sprintf('VALUES(%s)', $column);
            }
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
                $type = PDO::PARAM_STR;
                if (is_bool($value[$set])) {
                    $type = PDO::PARAM_BOOL;
                }
                $query->bindValue($index, $value[$set], $type);
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
                echo sprintf(' %s => %s (%s),', $key, $value[$set], gettype($value[$set]));
                ++$index;
            }
            echo "\n";
        }

        return $this;
    }
}