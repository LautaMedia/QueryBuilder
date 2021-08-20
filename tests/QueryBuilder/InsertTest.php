<?php
declare(strict_types=1);

namespace QueryBuilder;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use Query\Contract\Executor;
use function Query\insertIgnoreInto;
use function Query\insertInto;

class InsertTest extends TestCase
{

    public function testInsertOne(): void
    {
        $this->assertEquals(
            'INSERT INTO a(b) VALUES (?)',
            (string)insertInto('a')
                ->value('b', '')
        );
    }

    public function testInsertIgnore(): void
    {
        $this->assertEquals(
            'INSERT IGNORE INTO a(b) VALUES (?)',
            (string)insertIgnoreInto('a')
                ->value('b', '')
        );
    }

    public function testInsertMany(): void
    {
        $items = [1, 2];
        $query = insertInto('a');

        foreach ($items as $item) {
            $query = $query
                ->value('b', $item)
                ->value('c', $item);
        }
        $this->assertEquals(
            'INSERT INTO a(b, c) VALUES (?, ?), (?, ?)',
            (string)$query
        );
    }

    public function testBindMultiple(): void
    {
        $items = [1, 2];
        $query = insertInto('a');

        foreach ($items as $item) {
            $query = $query
                ->value('b', $item)
                ->value('c', $item);
        }

        $pdoMock = $this->createMock(Executor::class);
        $queryMock = $this->createMock(PDOStatement::class);
        $pdoMock->method('prepare')->willReturn($queryMock);
        $queryMock->expects($this->exactly(4))
            ->method('bindValue')
            ->withConsecutive(
                [$this->equalTo(1), $this->equalTo('1')],
                [$this->equalTo(2), $this->equalTo('1')],
                [$this->equalTo(3), $this->equalTo('2')],
                [$this->equalTo(4), $this->equalTo('2')],
                );

        /** @var Executor $pdoMock */
        $query->execute($pdoMock);
    }

    public function testBindBool(): void
    {
        $query = insertInto('a')->value('b', true);

        $pdoMock = $this->createMock(Executor::class);
        $queryMock = $this->createMock(PDOStatement::class);
        $pdoMock->method('prepare')->willReturn($queryMock);
        $queryMock->expects($this->once())
            ->method('bindValue')->with($this->equalTo(1), $this->equalTo(true), $this->equalTo(PDO::PARAM_BOOL));

        /** @var Executor $pdoMock */
        $query->execute($pdoMock);
    }
}