# QueryBuilder Documentation

The purpose of this query builder is to make it easy to write, reuse and abstract sql queries in PHP.

All QueryBuilder methods are globally available through  namespace.

This module is fully self-contained and does not have any internal or external dependencies.

The interface of this query builder is pretty straightforward if you have previous SQL experience.
It mostly mimics the MySQL syntax in PHP with a few exceptions and shortcuts.

## IMPORTANT

To prevent SQL injections read this section carefully.
Only the variable $anything in the three examples below will be prepared by the query builder and
can safely contain user defined values.


bind() in select, update and delete queries
```PHP
deleteFrom('table')
    ->where('column = :value')
    ->bind(':value', $anything);
```
value() in an insert query
```PHP
insertInto('table')
    ->value('column', $anything);
```
set() in an update query
```PHP
update('table')
    ->set('column', $anything);
```

## Select

### Table syntax

```PHP
function table(string $from, $alias=''){...}

// SELECT * FROM table
table('table');

// SELECT * FROM table alias
table('table', 'alias');

// SELECT column FROM table
table('table')->select('column')
```

### Select
As in SQL, select queries start with a select() method and optional parameters defining what you want to select.


```PHP
function select(string ...$parameters){...}

// SELECT *
select();

// SELECT 1, 2
select('1', '2');
```

### From


```PHP
function from(string $table_name, string $alias=''){...}

// SELECT * FROM table
select()->from('table');

// SELECT * FROM table alias
select()->from('table', 'alias');
```

### Where

```PHP
function where(string $conditions){...}

// SELECT * FROM table WHERE 1 = 1
select()->from('table')->where(equal('1', '1'));
```
this is equivalent to
```PHP
select()->from('table')->where('1 = 1');
```

Contrary to SQL syntax you can stack where functions.
```PHP
// SELECT * FROM table WHERE 1 = 1 AND 2 = 2
$query = select()->from('table')->where('1 = 1');
$query->where(equal('2', '2'));
```
This can be useful if you want to reuse conditions
```PHP
// SELECT * FROM table WHERE 1 = 1 AND 3 = 3
$query->where(equal('3', '3'));
```

### Joins
```PHP
function join(string $table, string $conditions, string $alias=''){...}
function leftJoin(string $table, string $conditions, string $alias=''){...}
function rightJoin(string $table, string $conditions, string $alias=''){...}

// SELECT * FROM table JOIN table2 ON table.id = table2.table1_id
select()->from('table')->join('table2', equal('table.id', 'table2.table1.id'));
```
Like where, joins can be stacked
```PHP
select()->from('table')->join(...)->leftJoin(...)->rightJoin(...);
```


### Order By
```PHP
function asc(string $column){...}
function desc(string $column){...}

// SELECT * FROM table ORDER BY row ASC, row2 DESC
select()->from('table')->asc('row')->desc('row2');
```

### Group By
```PHP
function group(string $column){...}

// SELECT * FROM table GROUP BY a
select()->from('table')->group('a');
```


### Having
Having works exactly like where, but it is applied later
Check MySql documentation for more information
```PHP
function having(string $conditions){...}

// SELECT * FROM table HAVING 1 = 1
select()->from('table')->having('1 = 1');
```

### Limit, Offset
```PHP
function limit(int $limit, int $offset=0){...}

// SELECT * FROM table LIMIT 5
select()->from('table')->limit(5);
// SELECT * FROM table LIMIT 5, 1
select()->from('table')->limit(5, 1);
```

### Union and alias

You can use unions with query builder just as you can with SQL
```PHP
$query->union($query);
$query->unionAll($query);
```

You can also define an alias for a subquery
```PHP
$query->as('alias');
```

### With
```PHP
function with(){...}
function withRecursive(){...}
function cte(string $name, Unbound $subQuery, array $columns=[]){...}
function query(Unbound $select){...}

// WITH cte1 (a, b) AS (SELECT 1, 2) SELECT 5, 6 
with()->cte('cte1', select(1, 2), ['a', 'b'])->select(5, 6)
// OR
with()->cte('cte1', select(1, 2), ['a', 'b'])->query(select(5, 6))
```


### Unsupported keywords

- Priority keywords
- INTO
- WINDOW
- PARTITION
- ROLLUP
- FOR

## Insert

Inserting is really straightforward. Values are automatically escaped.
```PHP
function insertInto(string $table){...}
function insertIgnoreInto(string $table){...}
function value(string $column, $value, bool $onDuplicateUpdate=false, string $to=''){...}

// INSERT INTO table(a, b, c) VALUES(1, 2, 3)
insertInto('table')
    ->value('a', 1)
    ->value('b', 2)
    ->value('c', 3)
    ->execute($db);
```
Inserting multiple values in a loop.
```PHP
// INSERT INTO table(a, b) VALUES (1, 1), (2, 2)
$query = insertInto('table');
$items = [1, 2];
foreach ($items as $item) {
    $query = $query
        ->value('b', $item)
        ->value('c', $item);
}
```

Update values on duplicate key
```PHP
// INSERT INTO table(a, b, c) VALUES(1, 2, 3) ON DUPLICATE KEY UPDATE a = VALUES(a), b = 1
insertInto('table')
    ->value('a', 1, true)
    ->value('b', 2, true, '1')
    ->value('c', 3)
    ->execute($db);
}
```

### Unsupported keywords

- Priority keywords
- PARTITION
- SELECT
- TABLE

## Update

Updating is really simple. set() function escapes values automatically
```PHP
function update(string $table){...}
function set(string $column, $value){...}

// UPDATE a SET b = 'c', d = 'e'
update('a')
    ->set('b', 'c')
    ->set('d', 'e');
```

If you need to give column value as SQL, you can do it with setRaw() function.
```PHP
function setRaw(string $column, string $rawSql){...}

// UPDATE table SET column = CURRENT_TIMESTAMP()
update('table')
    ->setRaw('column', 'CURRENT_TIMESTAMP()');
```


## Delete

Delete shares methods with Select From but does not have having() and limit() does not support offset
```PHP
// DELETE FROM table JOIN table2 ON table1.id = table2.table1_i WHERE column = value GROUP BY column LIMIT 5
deleteFrom('table')
    ->join('table2', equal('table1.id', 'table2.table1_id'))
    ->where(equal('column', 'value'))
    ->asc('column')
    ->limit(5);
```

### Unsupported keywords

- Priority keywords
- IGNORE
- PARTITION

## Binding values
This section is not applicable for INSERT queries.

```PHP
function bind(string $key, $value) {}

// Select query
$query = select()->from('table')->where(equal('column', ':value'));
$query->bind(':value', 123);

// Update query's set() function binds values magically
update('table')->set('column', 123);

// If you use WHERE with an UPDATE you still need to bind conditions manually
$query = update('table')->set('column', 123)->where(equal('column', ':value'));
$query->bind(':value', 123);

// EVEN THOUGH THIS WORKS, DON'T DO IT, USE set() INSTEAD

$query = update('table')->setRaw('column', ':value');
$query->bind(':value', 123);

// Delete query
$query = deleteFrom('table')->where(equal('column', ':value'));
$query->bind(':value', 123);
```

## Executing the query
execute() executes the query.
```PHP
function execute(Executor $connection): void {...}

$query->execute($dbConnection);
```
get() executes the query and returns a Result object

NOTE: This is only applicable for SELECT queries
```PHP
function get(Executor $connection): Result {...}

$result = $query->get($dbConnection);

// rows() returns an array with stdClass objects.
// it is equivalent to $pdo->fetchAll(\PDO::FETCH_OBJ);
$rows = $result->rows();
```

## SQL condition helpers

And and Or function have underscores because and and or are also PHP keywords.
All functions return valid and reusable SQL condition strings.

```PHP
function equal(string $value, string $value2): string {...}
function and_(string $value, string $value2): string {...}
function or_(string $value, string $value2): string {...}
function any(string ...$conditions): string {...}
function all(string ...$conditions): string {...}
function in(string $value, array $values): string {...}
function not(string $condition): string {...}
function isNull(string $value): string {...}

// (a) AND (b) AND (c) AND (d)'
all('a', 'b', 'c', 'd');

// a IS NULL
isNull('a');
```