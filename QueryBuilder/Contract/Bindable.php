<?php
declare(strict_types=1);

namespace Query\Contract;

interface Bindable extends SqlString {

    /** Bind value to a previously defined named parameter
     * @param string $key Parameter name
     * @param mixed $value Can safely be anything
     * @return static|Bindable
     */
    public function bind(string $key, $value): Bindable;

    public function binds(): array;
}