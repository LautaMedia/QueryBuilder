<?php
declare(strict_types=1);
namespace QueryBuilder;
use PHPUnit\Framework\TestCase;
use function Query\select;

class UnionTest extends TestCase
{
    public function testUnion(): void
    {
        $this->assertEquals(
            '(SELECT * FROM a) UNION (SELECT * FROM b)',
            (string)select()
                ->from('a')
                ->union(
                    select()
                        ->from('b')
                )
        );
    }

    public function testUnionLimit(): void
    {
        $this->assertEquals(
            '(SELECT * FROM a) UNION (SELECT * FROM b) LIMIT 2, 5',
            (string)select()
                ->from('a')
                ->union(
                    select()
                        ->from('b')
                )
                ->limit(5, 2)
        );
    }

    public function testUnionDesc(): void
    {
        $this->assertEquals(
            '(SELECT * FROM a) UNION (SELECT * FROM b) ORDER BY column DESC',
            (string)select()
                ->from('a')
                ->union(
                    select()
                        ->from('b')
                )
                ->desc('column')
        );
    }

    public function testUnionFilters(): void
    {
        $this->assertEquals(
            '(SELECT * FROM a) UNION (SELECT * FROM b) ORDER BY column1 ASC, column2 DESC LIMIT 2, 5',
            (string)select()
                ->from('a')
                ->union(
                    select()
                        ->from('b')
                )
                ->asc('column1')
                ->desc('column2')
                ->limit(5, 2)
        );
    }

    public function testUnionAsc(): void
    {
        $this->assertEquals(
            '(SELECT * FROM a) UNION (SELECT * FROM b) ORDER BY column ASC',
            (string)select()
                ->from('a')
                ->union(
                    select()
                        ->from('b')
                )
                ->asc('column')
        );
    }

    public function testUnionAll(): void
    {
        $this->assertEquals(
            '(SELECT * FROM a) UNION ALL (SELECT * FROM b)',
            (string)select()
                ->from('a')
                ->unionAll(
                    select()
                        ->from('b')
                )
        );
    }
}