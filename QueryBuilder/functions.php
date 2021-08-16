<?php
declare(strict_types=1);

namespace Query;

use Query\Contract\Bindable;
use Query\Contract\Insert as InsertInterface;
use Query\Contract\Joinable as DeleteInterface;
use Query\Contract\Settable;
use Query\Contract\Tableable;
use Query\Contract\Unionable;
use Query\Model\Condition\All;
use Query\Model\Condition\AndOp;
use Query\Model\Condition\Any;
use Query\Model\Condition\Equal;
use Query\Model\Condition\GreaterThan;
use Query\Model\Condition\In;
use Query\Model\Condition\IsNull;
use Query\Model\Condition\JsonObject;
use Query\Model\Condition\LessThan;
use Query\Model\Condition\Like;
use Query\Model\Condition\Not;
use Query\Model\Condition\OrOp;
use Query\Model\Query\Delete;
use Query\Model\Query\Insert;
use Query\Model\Query\Select;
use Query\Model\Query\Update;
use Query\Model\Query\With;

/**
 * @param string|Unionable $name
 * @param string $alias
 * @return Contract\Table
 */
function table($name, string $alias = ''): Contract\Table
{
    return (new Select)->select()->from($name, $alias);
}

/**
 * @param string|Unionable ...$fields
 * @return Tableable
 */
function select(...$fields): Tableable
{
    if (empty($fields)) {
        $fields = ['*'];
    }
    return (new Select)->select(...$fields);
}

function withRecursive(): Contract\With
{
    return new With(true);
}

function with(): Contract\With
{
    return new With();
}

function insertInto(string $table): InsertInterface
{
    return new Insert($table, false);
}

function insertIgnoreInto(string $table): InsertInterface
{
    return new Insert($table, true);
}

function deleteFrom(string $table, string $alias = ''): DeleteInterface
{
    return (new Delete)->from($table, $alias);
}

function update(string $table): Settable
{
    return new Update($table);
}

/**
 * @param string|Bindable ...$conditions
 * @return Bindable
 */
function all(...$conditions): Bindable
{
    return new All(...$conditions);
}

/**
 * @param string|Bindable $a
 * @param string|Bindable $b
 * @return Bindable
 */
function and_($a, $b): Bindable
{
    return new AndOp($a, $b);
}

/**
 * @param string|Bindable ...$conditions
 * @return Bindable
 */
function any(...$conditions): Bindable
{
    return new Any(...$conditions);
}

/**
 * @param string|Bindable $a
 * @param string|Bindable $b
 * @return Bindable
 */
function equal($a, $b): Bindable
{
    return new Equal($a, $b);
}

/**
 * @param string|Bindable $a
 * @param string|Bindable $b
 * @return Bindable
 */
function lessThan($a, $b): Bindable
{
    return new LessThan($a, $b);
}

/**
 * @param string|Bindable $a
 * @param string|Bindable $b
 * @return Bindable
 */
function greaterThan($a, $b): Bindable
{
    return new GreaterThan($a, $b);
}

/**
 * @param string|Bindable $a
 * @param string|Bindable $b
 * @return Bindable
 */
function like($a, $b): Bindable
{
    return new Like($a, $b);
}

/**
 * @param string|Bindable $a
 * @return Bindable
 */
function not($a): Bindable
{
    return new Not($a);
}

/**
 * @param string|Bindable $a
 * @param string|Bindable $b
 * @return Bindable
 */
function or_($a, $b): Bindable
{
    return new OrOp($a, $b);
}

/**
 * @param string|Bindable $key
 * @param array|Bindable $set
 * @return Bindable
 */
function in($key, $set): Bindable
{
    return new In($key, $set);
}

/**
 * @param string|Bindable $column
 * @return Bindable
 */
function isNull($column): Bindable
{
    return new IsNull($column);
}

function jsonObject(array $variables, string $as): string
{
    return (string)new JsonObject($variables, $as);
}
