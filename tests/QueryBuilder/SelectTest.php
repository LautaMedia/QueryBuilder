<?php
declare(strict_types=1);

namespace QueryBuilder;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use Query\Contract\Executor;
use function Query\select;

class SelectTest extends TestCase
{
    public function testSelectDefault(): void
    {
        $this->assertEquals(
            'SELECT *',
            (string)select()
        );
    }

    public function testSelectFields(): void
    {
        $this->assertEquals(
            'SELECT 1, 2',
            (string)select('1', '2')
        );
    }

    public function testFrom(): void
    {
        $this->assertEquals(
            'SELECT * FROM a',
            (string)select()
                ->from('a')
        );
    }

    public function testFromAlias(): void
    {
        $this->assertEquals(
            'SELECT * FROM a b',
            (string)select()
                ->from('a', 'b')
        );
    }

    public function testJoin(): void
    {
        $this->assertEquals(
            'SELECT * FROM a INNER JOIN b ON 1',
            (string)select()
                ->from('a')
                ->join('b', '1')
        );
    }

    public function testSelectAfterJoin(): void
    {
        $this->assertEquals(
            'SELECT *, c FROM a INNER JOIN b ON 1',
            (string)select()
                ->from('a')
                ->join('b', '1')
                ->select('c')
        );
    }

    public function testJoinWhere(): void
    {
        $this->assertEquals(
            'SELECT * FROM a INNER JOIN b ON 1 WHERE 1',
            (string)select()
                ->from('a')
                ->join('b', '1')
                ->where('1')
        );
    }

    public function testJoinWhereHaving(): void
    {
        $this->assertEquals(
            'SELECT * FROM a INNER JOIN b ON 1 WHERE 1 HAVING 1',
            (string)select()
                ->from('a')
                ->join('b', '1')
                ->where('1')
                ->having('1')
        );
    }

    public function testHavingMultiple(): void
    {
        $this->assertEquals(
            'SELECT * FROM a INNER JOIN b ON 1 WHERE 1 HAVING (1) AND (2)',
            (string)select()
                ->from('a')
                ->join('b', '1')
                ->where('1')
                ->having('1')
                ->having('2')
        );
    }

    public function testJoinAlias(): void
    {
        $this->assertEquals(
            'SELECT * FROM a INNER JOIN b c ON 1',
            (string)select()
                ->from('a')
                ->join('b', '1', 'c')
        );
    }

    public function testLeftJoin(): void
    {
        $this->assertEquals(
            'SELECT * FROM a LEFT JOIN b ON 1',
            (string)select()
                ->from('a')
                ->leftJoin('b', '1')
        );
    }

    public function testRightJoin(): void
    {
        $this->assertEquals(
            'SELECT * FROM a RIGHT JOIN b ON 1',
            (string)select()
                ->from('a')
                ->rightJoin('b', '1')
        );
    }

    public function testWhere(): void
    {
        $this->assertEquals(
            'SELECT * FROM a WHERE 1',
            (string)select()
                ->from('a')
                ->where('1')
        );
    }

    public function testMultipleWhere(): void
    {
        $this->assertEquals(
            'SELECT * FROM a WHERE (1) AND (2)',
            (string)select()
                ->from('a')
                ->where('1')
                ->where('2')
        );
    }

    public function testLimit(): void
    {
        $this->assertEquals(
            'SELECT * FROM a LIMIT 0, 1',
            (string)select()
                ->from('a')
                ->limit(1)
        );
    }

    public function testOffset(): void
    {
        $this->assertEquals(
            'SELECT * FROM a LIMIT 1, 1',
            (string)select()
                ->from('a')
                ->limit(1, 1)
        );
    }

    public function testBind(): void
    {
        $query = select()
            ->from('a')
            ->where('b = :b')
            ->bind(':b', 'c');
        $pdoMock = $this->createMock(Executor::class);
        $queryMock = $this->createMock(PDOStatement::class);
        $pdoMock->method('prepare')->willReturn($queryMock);
        $queryMock->expects($this->once())
            ->method('bindValue')
            ->with(
                $this->equalTo(':_0_b'),
                $this->equalTo('c')
            );

        /** @var Executor $pdoMock */
        $query->get($pdoMock);
    }

    public function testBindMultiple(): void
    {
        $query = select()
            ->from('a')
            ->where('b = :b0 AND c = :b1')
            ->bind(':b0', 'c')
            ->bind(':b1', 'd');
        $pdoMock = $this->createMock(Executor::class);
        $queryMock = $this->createMock(PDOStatement::class);
        $pdoMock->method('prepare')->willReturn($queryMock);
        $queryMock->expects($this->exactly(2))
            ->method('bindValue')
            ->withConsecutive(
                [$this->equalTo(':_0_b0'), $this->equalTo('c')],
                [$this->equalTo(':_1_b1'), $this->equalTo('d')]
            );
        /** @var Executor $pdoMock */
        $query->get($pdoMock);
    }

    public function testOrderByAsc(): void
    {
        $this->assertEquals(
            'SELECT * FROM a ORDER BY b ASC',
            (string)select()
                ->from('a')
                ->asc('b')
        );
    }

    public function testOrderByDesc(): void
    {
        $this->assertEquals(
            'SELECT * FROM a ORDER BY b DESC',
            (string)select()
                ->from('a')
                ->desc('b')
        );
    }

    public function testOrderByMany(): void
    {
        $this->assertEquals(
            'SELECT * FROM a ORDER BY b ASC, c DESC',
            (string)select()
                ->from('a')
                ->asc('b')
                ->desc('c')
        );
    }

    public function testGroupBy(): void
    {
        $this->assertEquals(
            'SELECT * FROM a GROUP BY b',
            (string)select()
                ->from('a')
                ->group('b')
        );
    }

    public function testGroupByMany(): void
    {
        $this->assertEquals(
            'SELECT * FROM a GROUP BY b, c',
            (string)select()
                ->from('a')
                ->group('b')
                ->group('c')
        );
    }

    public function testGroupByWhere(): void
    {
        $this->assertEquals(
            'SELECT * FROM a WHERE 1 GROUP BY b, c LIMIT 1, 1',
            (string)select()
                ->from('a')
                ->where('1')
                ->group('b')
                ->group('c')
                ->limit(1, 1)
        );
    }

    public function testFullQuery(): void
    {
        $this->assertEquals(
            'SELECT * FROM a b LEFT JOIN c d ON 1 RIGHT JOIN e f ON 2 INNER JOIN g h ON 3 WHERE (4) AND (5) GROUP BY i, j HAVING (6) AND (7) ORDER BY k ASC, l DESC LIMIT 9, 8',
            (string)select()
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
}
