<?php
declare(strict_types=1);

namespace Query\Model\Condition;


use Query\Contract\SqlString;

class JsonObject implements SqlString
{
    private array $variables;
    private string $as;

    public function __construct(array $variables, string $as)
    {
        $this->variables = $variables;
        $this->as = $as;
    }

    public function __toString(): string
    {
        $query = '';
        foreach($this->variables as $key => $value) {
            $query .= sprintf(empty($query) ? "'%s', %s" : ", '%s', %s", $key, $value);
        }

        return sprintf('JSON_OBJECT(%s) as %s', $query, $this->as);
    }
}