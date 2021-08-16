<?php
declare(strict_types=1);


namespace QueryBuilder;


use PHPUnit\Framework\TestCase;
use function Query\select;
use function Query\with;
use function Query\withRecursive;

class WithTest extends TestCase
{

    public function testWith(): void
    {
        $this->assertEquals(
            'WITH cte1 (a, b) AS (SELECT 1, 2), cte2 AS (SELECT 3, 4) SELECT 5, 6',
            (string) with()
                ->cte("cte1", select('1', '2'), ['a', 'b'])
                ->cte("cte2", select('3', '4'))
                ->select('5', '6')
        );
    }

    public function testWithRecursive(): void
    {
        $this->assertEquals(
            'WITH RECURSIVE cte (a, b) AS (SELECT 1, 2) SELECT 3, 4',
            (string) withRecursive()->cte("cte", select('1', '2'), ['a', 'b'])->select('3', '4')
        );
    }

    public function testWithFullQuery(): void
    {
        $this->assertEquals(
            'WITH cte AS (SELECT 1) SELECT * FROM a b LEFT JOIN c d ON 1 RIGHT JOIN e f ON 2 INNER JOIN g h ON 3 WHERE (4) AND (5) GROUP BY i, j HAVING (6) AND (7) ORDER BY k ASC, l DESC LIMIT 9, 8',
            (string)with()->cte("cte", select('1'))
                ->select()
                ->from('a', 'b')
                ->leftJoin('c', '1', 'd')
                ->rightJoin('e', '2', 'f')
                ->join('g', '3', 'h')
                ->where('4')
                ->where('5')
                ->group('i')
                ->group('j')
                ->having('6')
                ->having('7')
                ->asc('k')
                ->desc('l')
                ->limit(8, 9)
        );
    }

    public function testWithQuery(): void {
        $this->assertEquals(
            'WITH cte AS (SELECT 1) SELECT * FROM a b LEFT JOIN c d ON 1 RIGHT JOIN e f ON 2 INNER JOIN g h ON 3 WHERE (4) AND (5) GROUP BY i, j HAVING (6) AND (7) ORDER BY k ASC, l DESC LIMIT 9, 8',
            (string)with()->cte("cte", select('1'))
                ->query(select()
                ->from('a', 'b')
                ->leftJoin('c', '1', 'd')
                ->rightJoin('e', '2', 'f')
                ->join('g', '3', 'h')
                ->where('4')
                ->where('5')
                ->group('i')
                ->group('j')
                ->having('6')
                ->having('7')
                ->asc('k')
                ->desc('l')
                ->limit(8, 9))
        );
    }
}