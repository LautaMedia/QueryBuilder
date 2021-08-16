<?php
declare(strict_types=1);

namespace QueryBuilder;

use PDOStatement;
use PHPUnit\Framework\TestCase;
use Query\Contract\Executor;
use function Query\update;

class UpdateTest extends TestCase
{
    public function testUpdateRaw(): void
    {
        $this->assertEquals(
            'UPDATE a SET b = c',
            (string)update('a')
                ->setRaw('b', 'c')
        );
    }

    public function testUpdateManyRaw(): void
    {
        $this->assertEquals(
            'UPDATE a SET b = c, d = e',
            (string)update('a')
                ->setRaw('b', 'c')
                ->setRaw('d', 'e')
        );
    }

    public function testUpdateWhereRaw(): void
    {
        $this->assertEquals(
            'UPDATE a SET b = c WHERE 1',
            (string)update('a')
                ->setRaw('b', 'c')
                ->where('1')
        );
    }

    public function testUpdate(): void
    {
        $this->assertEquals(
            'UPDATE a SET b = :_0_b',
            (string)update('a')
                ->set('b', 'c')
        );
    }

    public function testUpdateMany(): void
    {
        $this->assertEquals(
            'UPDATE a SET b = :_0_b, d = :_1_d',
            (string)update('a')
                ->set('b', 'c')
                ->set('d', 'e')
        );
    }

    public function testUpdateWhere(): void
    {
        $this->assertEquals(
            'UPDATE a SET b = :_0_b WHERE 1',
            (string)update('a')
                ->set('b', 'c')
                ->where('1')
        );
    }

    public function testUpdateBinds(): void
    {
        $query = update('a')
            ->set('b', 'c');

        $pdoMock = $this->createMock(Executor::class);
        $queryMock = $this->createMock(PDOStatement::class);
        $pdoMock->method('prepare')->willReturn($queryMock);
        $queryMock->expects($this->once())
            ->method('bindValue')
            ->withConsecutive(
                [$this->equalTo(':_0_b'), $this->equalTo('c')],
                );

        /** @var Executor $pdoMock */
        $query->execute($pdoMock);
    }

    public function testComplexUpdateBinds(): void
    {
        $query = update('a')
            ->set('b', 'c')
            ->where('c = :d')
            ->bind(':d', 'e');

        $pdoMock = $this->createMock(Executor::class);
        $queryMock = $this->createMock(PDOStatement::class);
        $pdoMock->method('prepare')->willReturn($queryMock);
        $queryMock->expects($this->exactly(2))
            ->method('bindValue')
            ->withConsecutive(
                [$this->equalTo(':_0_b'), $this->equalTo('c')],
                [$this->equalTo(':_1_d'), $this->equalTo('e')]
            );

        /** @var Executor $pdoMock */
        $query->execute($pdoMock);
    }
}