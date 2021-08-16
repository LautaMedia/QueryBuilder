<?php
declare(strict_types=1);

namespace QueryBuilder;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use Query\Contract\Executor;
use function Query\deleteFrom;

class DeleteTest extends TestCase
{
    public function testDeleteFrom(): void
    {
        $this->assertEquals(
            'DELETE FROM a',
            (string)deleteFrom('a')
        );
    }

    public function testAlias(): void
    {
        $this->assertEquals(
            'DELETE FROM a b',
            (string)deleteFrom('a', 'b')
        );
    }

    public function testJoin(): void
    {
        $this->assertEquals(
            'DELETE FROM a INNER JOIN b ON 1',
            (string)deleteFrom('a')
                ->join('b', '1')
        );
    }

    public function testJoinWhere(): void
    {
        $this->assertEquals(
            'DELETE FROM a INNER JOIN b ON 1 WHERE 1',
            (string)deleteFrom('a')
                ->join('b', '1')
                ->where('1')
        );
    }

    public function testWhere(): void
    {
        $this->assertEquals(
            'DELETE FROM a WHERE 1',
            (string)deleteFrom('a')
                ->where('1')
        );
    }

    public function testBind(): void
    {
        $query = deleteFrom('a')
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
}