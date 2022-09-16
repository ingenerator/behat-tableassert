<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   BSD-3-Clause
 */

namespace test\Ingenerator\BehatTableAssert\TableParser\HTML;


use Ingenerator\BehatTableAssert\TableNode\PaddedTableNode;
use Ingenerator\BehatTableAssert\TableParser\HTML\HTMLStringTableParser;
use test\Ingenerator\BehatTableAssert\TableParser\TableParserTest;

class HTMLStringTableParserTest extends TableParserTest
{
    public function test_it_is_initialisable()
    {
        $this->assertInstanceOf(
            'Ingenerator\BehatTableAssert\TableParser\HTML\HTMLStringTableParser',
            $this->newSubject()
        );
    }

    /**
     * @testWith [""]
     *           [null]
     *           [1]
     */
    public function test_it_throws_when_parsing_non_or_empty_string($value)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected an HTML string');

        $this->newSubject()->parse($value);
    }

    /**
     * @testWith ["random", false]
     *           ["random", true]
     *           ["<div></div>", false]
     *           ["<table><thead><tr><td>1</td></tr></thead><tbody></tbody></table>", false]
     */
    public function test_it_always_restores_state_of_libxml_error_handling(
        $html,
        $use_errors_before
    ) {
        $old_setting = \libxml_use_internal_errors($use_errors_before);
        try {
            $this->newSubject()->parse($html);
        } catch (\Exception $e) { /* ignore */
        }
        $errors_after     = \libxml_get_errors();
        $use_errors_after = \libxml_use_internal_errors($old_setting);

        $this->assertSame([], $errors_after, 'Should clear libxml errors');
        $this->assertEquals(
            $use_errors_before,
            $use_errors_after,
            'Should restore libxml_use_internal_errors'
        );
    }

    public function test_it_throws_when_parsing_html_that_is_not_a_table()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected a <table>');

        $this->newSubject()->parse('<div></div>');
    }

    public function test_it_throws_when_parsing_table_without_thead()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No <thead> found in <table>');

        $this->newSubject()->parse('<table></table>');
    }

    public function test_it_throws_when_parsing_table_without_tbody()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No <tbody> found in <table>');

        $this->newSubject()->parse(
            '<table><thead><tr><th>Stuff</th></tr></thead></table>'
        );
    }

    public function test_it_throws_when_parsing_table_with_no_row_in_thead()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No <tr> found in <thead>');

        $this->newSubject()->parse(
            '
            <table>
                <thead></thead>
                <tbody></tbody>
            </table>
            '
        );
    }

    public function test_it_throws_when_parsing_table_with_multiple_rows_in_thead()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Multiple <tr> found in <thead>');

        $this->newSubject()->parse(
            '
            <table>
                <thead>
                    <tr></tr>
                    <tr></tr>
                </thead>
                <tbody></tbody>
            </table>
            '
        );
    }

    public function test_it_throws_when_parsing_table_with_no_cells()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('All table rows were empty');

        $this->newSubject()->parse('<table><thead><tr></tr></thead><tbody></tbody></table>');
    }

    public function provider_valid_html_tables()
    {
        return [
            'Single header cell, no body rows' =>
            [
                '<table><thead><tr><th>Header1</th></tr></thead><tbody></tbody></table>',
                [['Header1']]
            ],
            'Mix of th and td in header' =>
            [
                '<table><thead><tr><td>Header1</td><th>Header2</th></tr></thead><tbody></tbody></table>',
                [['Header1', 'Header2']]
            ],
            'Header and one row with mixed td and th' =>
            [
                '<table>'.
                '<thead><tr><td>Header1</td><th>Header2</th></tr></thead>'.
                '<tbody>'.
                '<tr><td>Cell1</td><td>Cell2</td></tr>'.
                '</tbody></table>',
                [
                    ['Header1', 'Header2'],
                    ['Cell1', 'Cell2']
                ]
            ],
            'Header and two rows, all td' =>
            [
                '<table>'.
                '<thead><tr><td>Header1</td><td>Header2</td></tr></thead>'.
                '<tbody>'.
                '<tr><td>1.1</td><td>1.2</td></tr>'.
                '<tr><td>2.1</td><td>2.2</td></tr>'.
                '</tbody></table>',
                [
                    ['Header1', 'Header2'],
                    ['1.1', '1.2'],
                    ['2.1', '2.2']
                ]
            ],
            'Header and one row, mixed th and td in head and body' =>
            [
                '<table>'.
                '<thead><tr><th>Header1</th><td>Header2</td></tr></thead>'.
                '<tbody>'.
                '<tr><th>1.1</th><td>1.2</td></tr>'.
                '</tbody></table>',
                [
                    ['Header1', 'Header2'],
                    ['1.1', '1.2'],
                ]
            ],
            'HTML escaped characters' =>
            [
                '<table><thead><tr><th>One &amp; Two &gt; None</th></tr></thead><tbody></tbody></table>',
                [
                    ['One & Two > None'],
                ]
            ],
            'Comments' =>
            [
                '<table><thead><tr><th>With <!--Comment --> inside</th></tr></thead><tbody></tbody></table>',
                [
                    ['With inside'],
                ]
            ],
            'Unicode characters, unescaped' =>
            [
                '<table><thead><tr><th>It works ✓</th></tr></thead><tbody></tbody></table>',
                [
                    ['It works ✓'],
                ]
            ],
            'Empty child nodes' =>
            [
                '<table><thead><tr><th>Nothing between <span></span> this</th></tr></thead><tbody></tbody></table>',
                [
                    ['Nothing between this'],
                ]
            ],
            'Nested child nodes with text content' =>
            [
                '<table><thead><tr><th>Nothing <span>between</span> this</th></tr></thead><tbody></tbody></table>',
                [
                    ['Nothing between this'],
                ]
            ],
            'Cells containing (valid) unclosed HTML tags' =>
            [
                '<table><thead><tr><th><input type="checkbox">Select</th></tr></thead><tbody></tbody></table>',
                [
                    ['Select'],
                ]
            ]
        ];
    }

    /**
     * @dataProvider provider_valid_html_tables
     */
    public function test_it_parses_table_node_from_valid_html_table($html, $expect_table)
    {
        $table = $this->newSubject()->parse($html);
        $this->assertTableWithRows($expect_table, $table);
    }

    public function provider_tables_with_whitespace()
    {
        return [
            // Whitespace between elements
            [
                "<table>   <thead>
                        <tr><td>Header1</td>
                    <td>Header2</td></tr>
                </thead><tbody><tr><td>Cell1</td>

                           <td>Cell2</td>
                    \t</tr></tbody></table>
                ",
                [
                    ['Header1', 'Header2'],
                    ['Cell1', 'Cell2']
                ]
            ],
            // Leading and trailing space inside elements
            [
                "<table><thead><tr><td>  Header1</td><td>Header2
                    </td></tr></thead>
                    <tbody><tr><td>
                       \tCell1   </td><td>   Cell2</td></tr></tbody></table>",
                [
                    ['Header1', 'Header2'],
                    ['Cell1', 'Cell2']
                ]
            ],
            // Multiple whitespace inside string
            [
                "<table><thead><tr><td>Head  1</td><td>Head     2</td></tr></thead>
                    <tbody><tr><td>Cell 1</td><td>Cell          2</td></tr></tbody></table>",
                [
                    ['Head 1', 'Head 2'],
                    ['Cell 1', 'Cell 2']
                ]
            ],
            // Non-space whitespace inside string
            [
                "<table><thead><tr><td>Head\t1</td><td>Head\t\t\t2</td></tr></thead>
                    <tbody><tr><td>Cell
                    1</td><td>Cell

                    2</td></tr></tbody></table>",
                [
                    ['Head 1', 'Head 2'],
                    ['Cell 1', 'Cell 2']
                ]
            ],
        ];
    }

    /**
     * @dataProvider provider_tables_with_whitespace
     */
    public function test_it_collapses_extra_whitespace_in_valid_html_table($html, $expect_table)
    {
        $table = $this->newSubject()->parse($html);
        $this->assertTableWithRows($expect_table, $table);
    }

    public function test_it_fills_colspan_cells_with_continuation_mark()
    {
        $table = $this->newSubject()->parse(
            '
                <table><thead>
                    <tr><th>Col1</th><td>Col2</td><td>Col3</td></tr>
                </thead><tbody>
                    <tr><th colspan="2">Stuff</th><td>Stuff3</td></tr>
                </tbody></table>
            '
        );
        $this->assertTableWithRows(
            [
                ['Col1', 'Col2', 'Col3'],
                ['Stuff', '...', 'Stuff3']
            ],
            $table
        );
    }

    public function test_it_fills_missing_table_cells()
    {
        $table = $this->newSubject()->parse(
            '
                <table><thead>
                    <tr><th>Column 1</th></tr>
                </thead><tbody>
                    <tr><th>Column 1</th><td>Column 2</td></tr>
                </tbody></table>'
        );
        $this->assertTableWithRows(
            [
                ['Column 1', PaddedTableNode::EMPTY_CELL_STRING],
                ['Column 1', 'Column 2'],
            ],
            $table
        );
    }

    public function test_it_fills_empty_table_rows()
    {
        $table = $this->newSubject()->parse(
            '
                <table><thead>
                    <tr><th>Column 1</th></tr>
                </thead><tbody>
                    <tr></tr>
                    <tr><th>Column 1</th></tr>
                </tbody></table>'
        );
        $this->assertTableWithRows(
            [
                ['Column 1'],
                [PaddedTableNode::EMPTY_CELL_STRING],
                ['Column 1'],
            ],
            $table
        );
    }

    public function test_it_optionally_ignores_rows_marked_with_data_behat_table_attributes()
    {
        $table = $this->newSubject()->parse(
            '
                <table><thead>
                    <tr data-behat-table="ignore"><th colspan="2">Top heading</th><td>Col3</td></tr>
                    <tr><th>Col1</th><th>Col2</th><td>Col3</td></tr>
                </thead><tbody>
                    <tr><td>A1</td><td>B1</td><td>C1</td></tr>
                    <tr data-behat-table="ignore"><td>A2</td><td>B2</td><td>C2</td></tr>
                    <tr><td>A3</td><td>B3</td><td>C3</td></tr>
                </tbody></table>
            '
        );
        $this->assertTableWithRows(
            [
                ['Col1', 'Col2', 'Col3'],
                ['A1', 'B1', 'C1'],
                ['A3', 'B3', 'C3'],
            ],
            $table
        );
    }

    public function test_it_optionally_prefixes_cell_text_content()
    {
        $table = $this->newSubject()->parse(
            '
                <table><thead>
                    <tr><th data-behat-table-prefix="First">Col1</th><th>Other</th></tr>
                </thead><tbody>
                    <tr><th>A1</th><td data-behat-table-prefix="Stuff">A2</td></tr>
                </tbody></table>
            '
        );
        $this->assertTableWithRows(
            [
                ['First Col1', 'Other'],
                ['A1', 'Stuff A2']
            ],
            $table
        );
    }

    protected function newSubject()
    {
        return new HTMLStringTableParser;
    }

}
