<?php
declare(strict_types=1);


namespace QueryBuilder;


use PHPUnit\Framework\TestCase;
use function Query\deleteFrom;
use function Query\insertInto;
use function Query\select;
use function Query\table;

class DebugTest extends TestCase
{
    public function testSelectDebug(): void
    {
        ob_start();
        table('a')->where('b = :c')->bind(':c', 'd')->debug();
        $this->assertEquals("SELECT * FROM a WHERE b = :_0_c\n:_0_c => d (string)\n", ob_get_clean());
    }

    public function testInsertDebug(): void
    {
        ob_start();
        insertInto('a')->value('b', 'c')->value('b', 'd')->debug();
        $this->assertEquals("INSERT INTO a(b) VALUES (?), (?)\n1: b => c (string),\n2: b => d (string),\n", ob_get_clean());
    }

    public function testDeleteDebug(): void
    {
        ob_start();
        deleteFrom('a')->where('b = :c')->bind(':c', 'd')->debug();
        $this->assertEquals("DELETE FROM a WHERE b = :_0_c\n:_0_c => d (string)\n", ob_get_clean());
    }

    public function testUnionDebug(): void
    {
        ob_start();
        table('a')->where('b = :c')->union(select('1'))->bind(":c", 'd')->debug();
        $this->assertEquals("(SELECT * FROM a WHERE b = :_0_c) UNION (SELECT 1)\n:_0_c => d (string)\n", ob_get_clean());
    }
}