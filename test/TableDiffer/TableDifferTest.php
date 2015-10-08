<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   BSD-3-Clause
 */

namespace test\Ingenerator\BehatTableAssert\TableDiffer;


use Ingenerator\BehatTableAssert\TableDiffer\TableDiffer;
use Ingenerator\BehatTableAssert\TableNode\PaddedTableNode;

class TableDifferTest extends \PHPUnit_Framework_TestCase
{

    public function test_it_is_initialisable()
    {
        $this->assertInstanceOf(
            '\Ingenerator\BehatTableAssert\TableDiffer\TableDiffer',
            $this->newSubject()
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_it_throws_with_unknown_option()
    {
        $this->newSubject()->diff(
            $this->parseTableString("A"),
            $this->parseTableString("B"),
            ['youCantJustMakeStuffUp' => 'No, really']
        );
    }

    /**
     * @testWith ["A"]
     *           ["A,B"]
     *           ["A,B\nOne,Two"]
     */
    public function test_it_finds_no_difference_in_identical_tables($table)
    {
        $this->assertNoDifference(
            $table,
            $table,
            []
        );
    }

    /**
     * @testWith ["A,B", "A", "Missing columns: 'B' (got 'A')"]
     *           ["A,B,C", "A", "Missing columns: 'B', 'C' (got 'A')"]
     *           ["A,B,C\n1,2,3", "A,C\n1,3", "Missing columns: 'B' (got 'A', 'C')"]
     */
    public function test_it_finds_structural_difference_with_missing_columns(
        $expect,
        $actual,
        $expect_msg
    ) {
        $this->assertOnlyStructuralDifferences(
            [$expect_msg],
            $this->parseTableString($expect),
            $this->parseTableString($actual)
        );
    }

    /**
     * @testWith ["A", "A,B", "Unexpected columns: 'B'"]
     *           ["A,D", "A,C,D", "Unexpected columns: 'C'"]
     *           ["A,D\n1,2", "A,C,D\n1,2,3", "Unexpected columns: 'C'"]
     */
    public function test_it_finds_structural_difference_with_additional_columns(
        $expect,
        $actual,
        $expect_msg
    ) {
        $this->assertOnlyStructuralDifferences(
            [$expect_msg],
            $this->parseTableString($expect),
            $this->parseTableString($actual)
        );
    }

    public function test_it_optionally_ignores_additional_columns()
    {
        $this->assertNoDifference(
            "A,B\n1,2",
            "A,B,C\n1,2,3",
            ['ignoreExtraColumns' => TRUE]
        );
    }

    public function test_it_finds_structural_differences_with_missing_and_additional_columns()
    {
        $this->assertOnlyStructuralDifferences(
            [
                "Missing columns: 'D' (got 'A', 'B', 'C')",
                "Unexpected columns: 'C'"
            ],
            $this->parseTableString("A,B,D"),
            $this->parseTableString("A,B,C")
        );

    }

    public function test_it_finds_structural_difference_with_out_of_sequence_columns()
    {
        $this->assertOnlyStructuralDifferences(
            [
                "Unexpected column sequence:\n".
                " - Expected: 'A', 'B', 'D'\n".
                " - Got:      'A', 'D', 'B'"
            ],
            $this->parseTableString("A,B,D"),
            $this->parseTableString("A,D,B")
        );
    }

    public function test_it_optionally_ignores_column_sequence()
    {
        $this->assertNoDifference(
            "A,B\n1,2\n3,4",
            "B,A\n2,1\n4,3",
            ['ignoreColumnSequence' => TRUE]
        );
    }

    public function test_it_finds_structural_difference_with_additional_row_at_end()
    {
        $this->assertOnlyStructuralDifferences(
            [
                'Additional row #3 (got: 5, 6)'
            ],
            $this->parseTableString("A,B\n1,2\n3,4"),
            $this->parseTableString("A,B\n1,2\n3,4\n5,6")
        );
    }

    public function test_it_finds_structural_difference_with_missing_row_at_end()
    {
        $this->assertOnlyStructuralDifferences(
            [
                'Missing row #3 (expected: 5, 6)'
            ],
            $this->parseTableString("A,B\n1,2\n3,4\n5,6"),
            $this->parseTableString("A,B\n1,2\n3,4")
        );
    }

    public function test_it_finds_structural_difference_with_additional_row_in_middle()
    {
        $this->markTestIncomplete(
            'Additional rows within table currently produce diff on all subsequent rows'
        );
        $this->assertOnlyStructuralDifferences(
            [
                'Additional row #2 (got: 3, 4)'
            ],
            $this->parseTableString("A,B\n1,2\n5,6"),
            $this->parseTableString("A,B\n1,2\n3,4\n5,6")
        );
    }

    public function test_it_finds_structural_difference_with_missing_row_in_middle()
    {
        $this->markTestIncomplete(
            'Missing rows within table currently produce diff on all subsequent rows'
        );
    }

    public function test_it_finds_value_difference_with_different_cell_value()
    {
        $expected = $this->parseTableString("Wrong,B\n1,2");
        $actual   = $this->parseTableString("Wrong,B\nX,2");
        $this->assertSame(
            [
                ['type' => 'value', 'row' => 1, 'col' => 'Wrong', 'expect' => '1', 'actual' => 'X']
            ],
            $this->newSubject()->diff($expected, $actual)
        );
    }

    public function test_it_finds_multiple_value_differences_with_different_cell_values()
    {
        $expected = $this->parseTableString("One,Two\n1,2\n3,4");
        $actual   = $this->parseTableString("One,Two\nX,Z\nP,4");
        $this->assertSame(
            [
                ['type' => 'value', 'row' => 1, 'col' => 'One', 'expect' => '1', 'actual' => 'X'],
                ['type' => 'value', 'row' => 1, 'col' => 'Two', 'expect' => '2', 'actual' => 'Z'],
                ['type' => 'value', 'row' => 2, 'col' => 'One', 'expect' => '3', 'actual' => 'P']
            ],
            $this->newSubject()->diff($expected, $actual)
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_it_throws_with_uncallable_custom_comparator()
    {
        $this->newSubject()->diff(
            $this->parseTableString("A\n1"),
            $this->parseTableString("A\n1"),
            [
                'comparators' => [
                    'A' => '942'
                ]
            ]
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_it_throws_with_custom_comparator_for_unexpected_column()
    {
        $this->newSubject()->diff(
            $this->parseTableString("A\n1"),
            $this->parseTableString("A\n1"),
            [
                'comparators' => [
                    'Whatever' => function () {
                    }
                ]
            ]
        );
    }

    public function test_it_does_not_find_difference_when_custom_comparison_passes()
    {
        $this->assertNoDifference(
            "One,Two\n1,2\n3,4",
            "One,Two\n11,2\n13,4",
            [
                'comparators' => [
                    'One' => function ($expected, $actual) {
                        return ($expected == ($actual - 10));
                    }
                ]
            ]
        );
    }

    public function test_it_finds_value_difference_when_custom_comparison_fails()
    {
        $table    = "One,Two\n1,2\n3,4";
        $expected = $this->parseTableString($table);
        $actual   = $this->parseTableString($table);
        $this->assertSame(
            [
                ['type' => 'value', 'row' => 1, 'col' => 'One', 'expect' => '1', 'actual' => '1'],
                ['type' => 'value', 'row' => 2, 'col' => 'One', 'expect' => '3', 'actual' => '3']
            ],
            $this->newSubject()->diff(
                $expected,
                $actual,
                [
                    'comparators' => [
                        'One' => function ($expected, $actual) {
                            return FALSE;
                        }
                    ]
                ]
            )
        );
    }


    protected function newSubject()
    {
        return new TableDiffer;
    }

    /**
     * @param $expect
     *
     * @return \Ingenerator\BehatTableAssert\TableNode\PaddedTableNode
     */
    protected function parseTableString($expect)
    {
        $data = [];
        foreach (explode("\n", $expect) as $row) {
            $data[] = explode(',', $row);
        }
        $expected = new PaddedTableNode($data);

        return $expected;
    }

    /**
     * @param $expect_messages
     * @param $expected
     * @param $actual
     */
    protected function assertOnlyStructuralDifferences($expect_messages, $expected, $actual)
    {
        $expected_errors = [];
        foreach ($expect_messages as $message) {
            $expected_errors[] = [
                'type'    => 'structural',
                'message' => $message
            ];
        };
        $this->assertSame(
            $expected_errors,
            $this->newSubject()->diff($expected, $actual)
        );
    }

    /**
     * @param $expect
     * @param $actual
     * @param $options
     */
    protected function assertNoDifference($expect, $actual, $options)
    {
        $this->assertSame(
            [],
            $this->newSubject()->diff(
                $this->parseTableString($expect),
                $this->parseTableString($actual),
                $options
            )
        );
    }


}
