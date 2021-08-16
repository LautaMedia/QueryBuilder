<?php
declare(strict_types=1);
namespace QueryBuilder;
use PHPUnit\Framework\TestCase;
use function Query\all;
use function Query\and_;
use function Query\any;
use function Query\equal;
use function Query\greaterThan;
use function Query\in;
use function Query\isNull;
use function Query\jsonObject;
use function Query\lessThan;
use function Query\like;
use function Query\not;
use function Query\or_;

class ConditionTest extends TestCase
{
    public function testAll(): void
    {
        $this->assertEquals(
            '(a) AND (b) AND (c) AND (d)',
            all('a', 'b', 'c', 'd')
        );
    }

    public function testAnd()
    {
        $this->assertEquals(
            '(a) AND (b)',
            and_('a', 'b')
        );
    }

    public function testAny(): void
    {
        $this->assertEquals(
            '(a) OR (b) OR (c) OR (d)',
            any('a', 'b', 'c', 'd')
        );
    }

    public function testEqual(): void
    {
        $this->assertEquals(
            'a = b',
            equal('a', 'b')
        );
    }

    public function testLike()
    {
        $this->assertEquals(
            'a LIKE b',
            like('a', 'b')
        );
    }

    public function testNot()
    {
        $this->assertEquals(
            'NOT a',
            not('a')
        );
    }

    public function testOr()
    {
        $this->assertEquals(
            '(a) OR (b)',
            or_('a', 'b')
        );
    }

    public function testIn(): void
    {
        $this->assertEquals(
            'a IN (b, c, d)',
            in('a', ['b', 'c', 'd'])
        );
    }

    public function testIsNull(): void
    {
        $this->assertEquals(
            'a IS NULL',
            isNull('a')
        );
    }

    public function testJsonObject(): void
    {
        $this->assertEquals(
            "JSON_OBJECT('a', b, 'c', d) as e",
            jsonObject(['a' => 'b', 'c' => 'd'], 'e')
        );
    }

    public function testGreaterThan(): void {
        $this->assertEquals(
            "a > b",
            greaterThan("a", "b")
        );
    }

    public function testLessThan(): void {
        $this->assertEquals(
            "a < b",
            lessThan("a", "b")
        );
    }
}