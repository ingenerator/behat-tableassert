<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   BSD-3-Clause
 */

namespace test\Ingenerator\BehatTableAssert;

use Ingenerator\BehatTableAssert\AssertTable;
use Ingenerator\BehatTableAssert\TableAssertionFailureException;
use Ingenerator\BehatTableAssert\TableNode\PaddedTableNode;

/**
 * @package test\Ingenerator\BehatTableAssert
 * @group   integration
 */
class AssertTableTest extends \PHPUnit\Framework\TestCase
{

    public function test_it_is_initialisable()
    {
        $this->assertInstanceOf(
            '\Ingenerator\BehatTableAssert\AssertTable',
            $this->newSubject()
        );
    }

    public function test_it_asserts_same()
    {
        $this->assertPasses(
            function (AssertTable $subject) {
                $subject->isSame(
                    new PaddedTableNode([['A', 'B'], ['1', '2']]),
                    new PaddedTableNode([['A', 'B'], ['1', '2']])
                );
            }
        );
        $this->assertFails(
            '/Failed asserting that two tables were identical: they should be.+?Unexpected column sequence/s',
            function (AssertTable $subject) {
                $subject->isSame(
                    new PaddedTableNode([['A', 'B'], ['1', '2']]),
                    new PaddedTableNode([['B', 'A'], ['2', '1']]),
                    'they should be'
                );
            }
        );
    }

    public function test_it_asserts_equals()
    {
        $this->assertPasses(
            function (AssertTable $subject) {
                $subject->isEqual(
                    new PaddedTableNode([['A', 'B'], ['1', '2']]),
                    new PaddedTableNode([['B', 'A'], ['2', '1']])
                );
            }
        );
        $this->assertFails(
            '/Failed asserting that two tables were equivalent: things.+?Missing column/s',
            function (AssertTable $subject) {
                $subject->isEqual(
                    new PaddedTableNode([['A', 'B'], ['1', '2']]),
                    new PaddedTableNode([['A'], ['2']]),
                    'things'
                );
            }
        );
    }

    public function test_it_asserts_contains_columns()
    {
        $this->assertPasses(
            function (AssertTable $subject) {
                $subject->containsColumns(
                    new PaddedTableNode([['A', 'B'], ['1', '2']]),
                    new PaddedTableNode([['B', 'A', 'C'], ['2', '1', '3']])
                );
            }
        );
        $this->assertFails(
            '/Failed asserting that a table contained expected data: reasons.+?\| X X \| X Y \|/s',
            function (AssertTable $subject) {
                $subject->containsColumns(
                    new PaddedTableNode([['A', 'B'], ['1', '2']]),
                    new PaddedTableNode([['A', 'B'], ['X', 'Y']]),
                    'reasons'
                );
            }
        );
    }

    public function test_it_asserts_is_comparable()
    {
        $this->assertPasses(
            function (AssertTable $subject) {
                $subject->isComparable(
                    new PaddedTableNode([['A', 'B'], ['1', '2']]),
                    new PaddedTableNode([['B', 'A'], ['2', '11']]),
                    [
                        'comparators'          => [
                            'A' => function ($expect, $actual) {
                                return $actual == ($expect + 10);
                            }
                        ],
                        'ignoreColumnSequence' => TRUE
                    ]
                );
            }
        );
        $this->assertFails(
            '/Failed comparing two tables: just because.+?\| X 1 \|.+?\| \^ 1 \|/s',
            function (AssertTable $subject) {
                $subject->isComparable(
                    new PaddedTableNode([['A', 'B'], ['1', '2']]),
                    new PaddedTableNode([['B', 'A'], ['2', '1']]),
                    [
                        'comparators'          => [
                            'A' => function ($expect, $actual) {
                                return $actual == ($expect + 10);
                            }
                        ],
                        'ignoreColumnSequence' => TRUE
                    ],
                    'just because'
                );
            }
        );
    }

    protected function newSubject()
    {
        return new AssertTable;
    }

    protected function assertPasses(callable $callback)
    {
        $result = call_user_func($callback, $this->newSubject());
        $this->assertNull($result, 'Assert methods should not return a value');
    }

    protected function assertFails($message_pattern, callable $callback)
    {
        try {
            call_user_func($callback, $this->newSubject());
            $this->fail('Expected a table assertion failure exception, none got');
        } catch (TableAssertionFailureException $e) {
            $this->assertRegExp($message_pattern, (string) $e);
        }
    }
}
