<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   BSD-3-Clause
 */

namespace test\Ingenerator\BehatTableAssert\TableParser\CSV;


use Behat\Gherkin\Node\TableNode;
use Ingenerator\BehatTableAssert\TableParser\CSV\CSVStreamTableParser;
use test\Ingenerator\BehatTableAssert\TableParser\TableParserTest;

class CSVStreamTableParserTest extends TableParserTest
{

    public function test_it_is_intialisable()
    {
        $this->assertInstanceOf('Ingenerator\BehatTableAssert\TableParser\CSV\CSVStreamTableParser', $this->newSubject());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_it_throws_if_asked_to_parse_a_string()
    {
        $this->newSubject()->parse('string');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_it_throws_if_asked_to_parse_non_stream_resource()
    {
        if ( ! function_exists('imagecreate')) {
            $this->markTestSkipped(
                'Cannot test with non-stream resource - gd functions not available'
            );
        }

        $stream = imagecreate(1, 1);
        try {
            $this->newSubject()->parse($stream);
        } finally {
            imagedestroy($stream);
        }
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_it_throws_if_asked_to_parse_closed_stream()
    {
        $stream = fopen('php://memory', 'w+');
        fclose($stream);
        $this->newSubject()->parse($stream);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage CSV was empty
     *
     * @testWith [""]
     *           ["\n"]
     *           ["\n\n"]
     */
    public function test_it_throws_when_parsing_empty_csv($empty_csv_string)
    {
        $this->tryParsing($empty_csv_string);
    }

    public function test_it_leaves_stream_at_original_position_after_use()
    {
        $stream = $this->givenStreamWith("row,one\nrow,two");
        fseek($stream, 4);
        try {
            $this->newSubject()->parse($stream);
            $this->assertEquals(4, ftell($stream));
        } finally {
            fclose($stream);
        }
    }

    public function provider_valid_csv_tables()
    {
        return [
            [
                "one",
                [['one']]
            ],
            [
                "one,two",
                [['one', 'two']]
            ],
            [
                "one\ntwo",
                [['one'], ['two']]
            ],
            [
                "one,two\nthree,four",
                [['one', 'two'], ['three', 'four']]
            ],
            [
                "one,two\nthree,four\n",
                [['one', 'two'], ['three', 'four']]
            ],
            [
                "one,\"two,three\"\nthree,four\n",
                [['one', 'two,three'], ['three', 'four']]
            ],
        ];
    }

    /**
     * @dataProvider provider_valid_csv_tables
     */
    public function test_it_parses_table_node_from_valid_csv_stream($csv_string, $expect_table_rows)
    {
        $table = $this->tryParsing($csv_string);
        $this->assertTableWithRows($expect_table_rows, $table);
    }

    public function provider_tables_with_missing_cells()
    {
        return [
            [
                "first,second,third\n".
                "one,two",
                [
                    ['first', 'second', 'third'],
                    ['one', 'two', '{empty}']
                ]
            ],
            [
                "first,second\n".
                "one,two,three",
                [
                    ['first', 'second', '{empty}'],
                    ['one', 'two', 'three']
                ]
            ],
            [
                "first,second,third\n".
                "one,two\n".
                "a,b,c",
                [
                    ['first', 'second', 'third'],
                    ['one', 'two', '{empty}'],
                    ['a', 'b', 'c']
                ]
            ],
        ];
    }

    /**
     * @dataProvider provider_tables_with_missing_cells
     */
    public function test_it_fills_missing_table_cells($csv_string, $expect_table_rows)
    {
        $table = $this->tryParsing($csv_string);
        $this->assertTableWithRows($expect_table_rows, $table);
    }

    public function test_it_fills_empty_table_rows_from_blank_lines_in_csv()
    {
        $table = $this->tryParsing(
            "a,b,c\n".
            "\n".
            "1,2,3"
        );
        $this->assertTableWithRows(
            [
                ['a', 'b', 'c'],
                ['{empty}', '{empty}', '{empty}'],
                ['1', '2', '3']
            ],
            $table
        );
    }

    public function test_it_fills_all_empty_table_rows_at_end_of_csv()
    {
        $table = $this->tryParsing(
            "a,b\n".
            "1,2\n".
            "\n".
            "\n"
        );

        $this->assertTableWithRows(
            [
                ['a', 'b'],
                ['1', '2'],
                ['{empty}', '{empty}'],
                ['{empty}', '{empty}'],
            ],
            $table
        );
    }

    protected function newSubject()
    {
        return new CSVStreamTableParser();
    }

    /**
     * @param string $string
     *
     * @return resource
     */
    protected function givenStreamWith($string)
    {
        $this->assertInternalType('string', $string, 'Must provide a string for '.__METHOD__);

        $stream = fopen('php://memory', 'w+');
        fwrite($stream, $string);

        return $stream;
    }

    /**
     * @param string $string
     *
     * @return \Behat\Gherkin\Node\TableNode
     */
    protected function tryParsing($string)
    {
        $stream = $this->givenStreamWith($string);

        try {
            $table = $this->newSubject()->parse($stream);
        } finally {
            fclose($stream);
        }

        return $table;
    }

}
