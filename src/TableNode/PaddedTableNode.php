<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   BSD-3-Clause
 */

namespace Ingenerator\BehatTableAssert\TableNode;


use Behat\Gherkin\Node\TableNode;

/**
 * Creates a TableNode where missing cells on any row are padded out with an {empty} marker so that
 * the overall table structure remains valid. Used when parsing dynamically generated tables (eg
 * from HTML or CSV) to allow for the common case that rendered output has different numbers of
 * columns on different rows eg due to an error with conditional rendering.
 *
 * [!!] This class not directly specified, see the specifications for the HTML and CSV table
 *      parsers.
 *
 * @package Ingenerator\BehatTableAssert\TableNode
 */
class PaddedTableNode extends TableNode
{
    const EMPTY_CELL_STRING = '{empty}';

    /**
     * @param array $rows
     */
    public function __construct(array $rows)
    {
        if ( ! $rows) {
            throw new \InvalidArgumentException('Table contained no rows');
        }

        if ( ! $column_count = max(array_map('count', $rows))) {
            throw new \InvalidArgumentException('All table rows were empty');
        }

        foreach ($rows as $index => $row) {
            $rows[$index] = array_pad($row, $column_count, self::EMPTY_CELL_STRING);
        }

        if (method_exists($this, 'addRow')) {
            // Support behat/gherkin ^2.0 - rows are assigned after construction
            parent::__construct();
            foreach ($rows as $row) {
                $this->addRow($row);
            }
        } else {
            // Support behat/gherkin ^3.0 - immutable table with rows assigned in constructor
            parent::__construct($rows);
        }
    }
}
