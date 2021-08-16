<?php
declare(strict_types=1);


namespace QueryBuilder;

use PHPUnit\Framework\TestCase;
use function Query\table;

class TableTest extends TestCase
{
    public function testTable(): void
    {
        $this->assertEquals(
            'SELECT * FROM a',
            (string)table('a')
        );
    }

    public function testTableAlias(): void
    {
        $this->assertEquals(
            'SELECT * FROM a b',
            (string)table('a', 'b')
        );
    }

    public function testTableSelectJoin(): void
    {
        $this->assertEquals(
            'SELECT c, g FROM a INNER JOIN d ON e = f',
            (string)table('a')->select('c')->join('d', 'e = f')->select('g')
        );
    }
}