<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   BSD-3-Clause
 */

namespace Ingenerator\BehatTableAssert\TableDiffer;


use Behat\Gherkin\Node\TableNode;
use Ingenerator\BehatTableAssert\TableNode\PaddedTableNode;

/**
 * Format the differences between two tables as a string for display.
 *
 * @package Ingenerator\BehatTableAssert\TableDiffer
 */
class DiffFormatter
{

    /**
     * @param array                         $diff
     * @param \Behat\Gherkin\Node\TableNode $actual
     *
     * @return string
     */
    public function format($diff, TableNode $actual)
    {
        if (empty($diff)) {
            throw new \InvalidArgumentException('Cannot format an empty diff');
        }

        if ($diff['structure']) {
            $diff_string = $this->formatStructureDiff($diff['structure'], (bool) $diff['values']);
        } else {
            $diff_string = '';
        }

        if ($diff['values']) {
            if ($diff_string) {
                $diff_string .=
                    "\n".
                    "\n".
                    "Cell differences:\n".
                    "-----------------\n";
            }
            $diff_string .= $this->formatValueDiff($diff, $actual);
        }

        return $diff_string;
    }

    /**
     * @param string[] $structural_differences
     * @param bool     $has_value_differences
     *
     * @return string
     */
    protected function formatStructureDiff($structural_differences, $has_value_differences)
    {
        if ((count($structural_differences) === 1) AND ! $has_value_differences) {
            return 'Structural difference: '.$structural_differences[0];
        }

        $string = [
            'Structural differences:',
            '-----------------------'
        ];
        foreach ($structural_differences as $message) {
            $string[] = ' - '.str_replace("\n", "\n   ", $message);
        }

        return implode("\n", $string);


    }

    /**
     * @param array                         $diff
     * @param \Behat\Gherkin\Node\TableNode $actual
     *
     * @return string
     */
    protected function formatValueDiff($diff, TableNode $actual)
    {
        $difference_columns = $this->findDifferenceColumns($diff, $actual->getRow(0));

        $output = [$this->formatDiffTableHeader($difference_columns)];

        foreach ($actual->getHash() as $row_index => $columns) {
            $actual_row   = [''];
            $expected_row = ['i'];
            foreach ($columns as $column => $value) {
                $cell_key        = $column.'#'.($row_index + 1);
                $has_differences = $difference_columns[$column];

                if (isset($diff['values'][$cell_key])) {
                    $actual_row[0]  = 'X';
                    $actual_row[]   = 'X '.$value;
                    $expected_row[] = '^ '.$diff['values'][$cell_key]['expect'];
                } else {
                    $actual_row[]   = $has_differences ? '  '.$value : $value;
                    $expected_row[] = $has_differences ? '  ~' : '~';
                }
            }

            $output[] = $actual_row;

            if ($actual_row[0] === 'X') {
                $output[] = $expected_row;
            }
        }

        $diff_string = (new PaddedTableNode($output))->__toString();

        return $diff_string;
    }

    /**
     * @param $diff
     * @param $all_columns
     *
     * @return array
     */
    protected function findDifferenceColumns($diff, $all_columns)
    {
        $difference_columns = array_fill_keys($all_columns, FALSE);
        foreach ($diff['values'] as $difference) {
            $difference_columns[$difference['col']] = TRUE;
        }

        return $difference_columns;
    }

    /**
     * @param $difference_columns
     *
     * @return array
     */
    protected function formatDiffTableHeader($difference_columns)
    {
        $header = [''];
        foreach ($difference_columns as $column2 => $has_differences2) {
            $header[] = $has_differences2 ? 'X '.$column2 : $column2;
        }

        return $header;
    }
}
