<?php
declare(strict_types=1);


namespace QueryBuilder;


use PHPUnit\Framework\TestCase;
use function Query\in;
use function Query\table;
use function Query\with;

class BindTest extends TestCase
{

    public function testBindFrom(): void {
        ob_start();
        table(table('a')
            ->where('b = :c')
            ->bind(':c', 'd')
            ->as('e')
        )->where('f = :d')
            ->bind(':d', 'g')
            ->debug();
        $this->assertEquals("SELECT * FROM (SELECT * FROM a WHERE b = :_0_c) AS e WHERE f = :_1_d\n:_0_c => d (string)\n:_1_d => g (string)\n", ob_get_clean());
    }

    public function testMultiBind(): void
    {
        ob_start();
        table('a')->where('b = :c')->bind(':c', 'd')->where('e = :c')->bind(':c', 'e')->debug();
        $this->assertEquals("SELECT * FROM a WHERE (b = :_0_c) AND (e = :_1_c)\n:_0_c => d (string)\n:_1_c => e (string)\n", ob_get_clean());
    }

    public function testUnionQuery(): void
    {
        ob_start();
        table('a')->where('b = :c')->bind(':c', 'd')->union(table('e')->where('f = :c')->bind(':c', 'g'))->debug();
        $this->assertEquals("(SELECT * FROM a WHERE b = :_0_c) UNION (SELECT * FROM e WHERE f = :_1_c)\n:_0_c => d (string)\n:_1_c => g (string)\n", ob_get_clean());
    }

    public function testSubQuery(): void
    {
        ob_start();
        table('a')
            ->select(
                table('b')
                    ->where('c = :d')
                    ->bind(':d', 'e')
                    ->as('f')
            )
            ->where('c = :d')
            ->bind(':d', 'f')
            ->debug();
        $this->assertEquals("SELECT (SELECT * FROM b WHERE c = :_0_d) AS f FROM a WHERE c = :_1_d\n:_0_d => e (string)\n:_1_d => f (string)\n", ob_get_clean());
    }

    public function testCombined(): void {
        ob_start();
        table('a')
            ->select(
                table('b')
                    ->where('c = :d')
                    ->bind(':d', 'e')
                    ->where('f = :d')
                    ->bind(':d', 'g')
                    ->as('h')
            )
            ->where('c = :d')
            ->bind(':d', 'i')
            ->union(
                table('e')
                    ->where('f = :d')
                    ->bind(':d', 'j'))
            ->debug();
        $this->assertEquals("(SELECT (SELECT * FROM b WHERE (c = :_0_d) AND (f = :_1_d)) AS h FROM a WHERE c = :_2_d) UNION (SELECT * FROM e WHERE f = :_3_d)\n:_0_d => e (string)\n:_1_d => g (string)\n:_2_d => i (string)\n:_3_d => j (string)\n", ob_get_clean());
    }

    public function testWhere(): void {
        ob_start();
        table('a')->where(in('b', table('c')->where('f = :e')->bind(':e', 'f')))->debug();
        $this->assertEquals("SELECT * FROM a WHERE b IN (SELECT * FROM c WHERE f = :_0_e)\n:_0_e => f (string)\n", ob_get_clean());
    }

    public function testHaving(): void {
        ob_start();
        table('a')->having(in('b', table('c')->where('d = :e')->bind(':e', 'f')))->debug();
        $this->assertEquals("SELECT * FROM a HAVING b IN (SELECT * FROM c WHERE d = :_0_e)\n:_0_e => f (string)\n", ob_get_clean());
    }

    public function testWith(): void {
        ob_start();
        with()->cte('a', table('b')->where('c = :d')->bind(':d', 'e'))->table('f')->having(in('g', table('h')->where('i = :j')->bind(':j', 'k')))->debug();
        $this->assertEquals("WITH a AS (SELECT * FROM b WHERE c = :_0_d) SELECT * FROM f HAVING g IN (SELECT * FROM h WHERE i = :_1_j)\n:_0_d => e (string)\n:_1_j => k (string)\n", ob_get_clean());
    }

    public function testQuery(): void {
        ob_start();
        with()->cte('a', table('b')->where('c = :d')->bind(':d', 'e'))->query(table('f')->having(in('g', table('h')->where('i = :j')->bind(':j', 'k'))))->debug();
        $this->assertEquals("WITH a AS (SELECT * FROM b WHERE c = :_0_d) SELECT * FROM f HAVING g IN (SELECT * FROM h WHERE i = :_1_j)\n:_0_d => e (string)\n:_1_j => k (string)\n", ob_get_clean());
    }
}