<?php
declare(strict_types=1);

namespace Query\Model\Query;

class Result implements \Query\Contract\Result
{
    private \PDOStatement $pdoStatement;
    private array $rows = [];
    private bool $cached = false;

    public function __construct(\PDOStatement $pdoStatement)
    {
        $this->pdoStatement = $pdoStatement;
        // This guarantees that the query is always executed
        $this->pdoStatement->execute();
    }

    public function rows(): array
    {
        if (!$this->cached) {
            $this->rows = $this->pdoStatement->fetchAll(\PDO::FETCH_OBJ);
        }

        return $this->rows;
    }
}