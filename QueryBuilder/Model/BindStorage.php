<?php
declare(strict_types=1);

namespace Query\Model;

use Query\Contract\Bindable;

class BindStorage
{
    private array $binds = [];

    /**
     * @param string|Bindable $bindable
     * @return string
     */
    public function merge($bindable): string {
        $queryString = (string) $bindable;
        if (is_string($bindable)) {
            return $bindable;
        }
        foreach ($bindable->binds() as $key => $value) {
            $queryString = $this->bind($queryString, $key, $value);
        }
        return $queryString;
    }

    /**
     * @param array $bindables
     * @return array
     */
    public function mergeMultiple($bindables): array {
        $values = [];
        foreach ($bindables as $bindable) {
            $values[] = $this->merge($bindable);
        }
        return $values;
    }

    /**
     * @param string $query
     * @param string $key
     * @param mixed $value
     * @return string
     */
    public function bind(string $query, string $key, $value): string {
        $offset = 0;
        while (($position = strpos($query, $key, $offset)) !== false) {
            /** @psalm-suppress PossiblyFalseOperand because it wil never be false */
            $scopedKey = sprintf(':_%s_%s', count($this->binds), substr($key, strrpos($key, '_') + 1));
            $query = substr_replace($query, $scopedKey, $position, strlen($key));
            $this->binds[$scopedKey] = $value;
            $offset = $position + strlen($scopedKey);
        }
        return $query;
    }

    public function binds(): array {
        return $this->binds;
    }
}