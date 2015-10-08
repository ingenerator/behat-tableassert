<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   BSD-3-Clause
 */

namespace test\Ingenerator\BehatTableAssert\TableDiffer;


use Behat\Gherkin\Node\PyStringNode;
use Ingenerator\BehatTableAssert\TableDiffer\DiffFormatter;
use Ingenerator\BehatTableAssert\TableNode\PaddedTableNode;

class DiffFormatterTest extends \PHPUnit_Framework_TestCase
{
    public function test_it_is_initialisable()
    {
        $this->assertInstanceOf(
            '\Ingenerator\BehatTableAssert\TableDiffer\DiffFormatter',
            $this->newSubject()
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_it_throws_with_empty_diff()
    {
        $this->newSubject()->format([], new PaddedTableNode([['A']]));
    }

    public function test_it_formats_single_structural_error_as_string()
    {
        $this->assertEquals(
            "Structural difference: Too many columns",
            $this->newSubject()->format(
                [
                    'structure' => [
                        'Too many columns'
                    ],
                    'values'    => []
                ],
                new PaddedTableNode([['A']])
            )
        );
    }

    public function test_it_formats_multiple_structural_errors_as_simple_list()
    {
        $this->assertEquals(
            "Structural differences:\n".
            "-----------------------\n".
            " - Too many columns\n".
            " - Missing column",
            $this->newSubject()->format(
                [
                    'structure' => [
                        'Too many columns',
                        'Missing column'
                    ],
                    'values'    => []
                ],
                new PaddedTableNode([['A']])
            )
        );
    }

    public function test_it_left_pads_new_lines_in_structural_error_messages()
    {
        $this->assertEquals(
            "Structural differences:\n".
            "-----------------------\n".
            " - Too many columns\n".
            " - Missing column\n".
            "   found it",
            $this->newSubject()->format(
                [
                    'structure' => [
                        'Too many columns',
                        "Missing column\nfound it",
                    ],
                    'values'    => []
                ],
                new PaddedTableNode([['A']])
            )
        );
    }

    public function test_it_formats_actual_table_with_inline_value_differences()
    {
        $expected = implode(
            "\n",
            [
                '|   | A | X B  | X C  | D  |',
                '|   | 1 |   2  |   3  | 4  |',
                '| X | 5 | X 9  | X 13 | 8  |',
                '| i | ~ | ^ 6  | ^ 7  | ~  |',
                '|   | 9 |   10 |   11 | 12 |',
            ]
        );
        $this->assertEquals(
            $expected,
            $this->newSubject()->format(
                [
                    'structure' => [],
                    'values'    => [
                        'B#2' => [
                            'col'    => 'B',
                            'row'    => '2',
                            'expect' => '6',
                            'actual' => '9'
                        ],
                        'C#2' => [
                            'col'    => 'C',
                            'row'    => '2',
                            'expect' => '7',
                            'actual' => '13'
                        ]
                    ]
                ],
                new PaddedTableNode(
                    [
                        ['A', 'B', 'C', 'D'],
                        ['1', '2', '3', '4'],
                        ['5', '9', '13', '8'],
                        ['9', '10', '11', '12']
                    ]
                )
            )
        );
    }

    public function test_it_formats_structural_differences_before_diff_table_when_both()
    {
        $expected = implode(
            "\n",
            [
                'Structural differences:',
                '-----------------------',
                ' - Problematic stuff',
                '',
                'Cell differences:',
                '-----------------',
                '|   | X A  | B  | X C  | D  |',
                '| X |   1  | 2  | X 13 | 4  |',
                '| i |   ~  | ~  | ^ 3  | ~  |',
                '|   |   5  | 6  |   7  | 8  |',
                '| X | X 19 | 10 |   11 | 12 |',
                '| i | ^ 9  | ~  |   ~  | ~  |',
            ]
        );
        $this->assertEquals(
            $expected,
            $this->newSubject()->format(
                [
                    'structure' => [
                        'Problematic stuff'
                    ],
                    'values'    => [
                        'C#1' => [
                            'col'    => 'C',
                            'row'    => '1',
                            'expect' => '3',
                            'actual' => '13'
                        ],
                        'A#3' => [
                            'col'    => 'A',
                            'row'    => '3',
                            'expect' => '9',
                            'actual' => '19'
                        ],
                    ]
                ],
                new PaddedTableNode(
                    [
                        ['A', 'B', 'C', 'D'],
                        ['1', '2', '13', '4'],
                        ['5', '6', '7', '8'],
                        ['19', '10', '11', '12']
                    ]
                )
            )
        );

    }

    protected function newSubject()
    {
        return new DiffFormatter;
    }

}
