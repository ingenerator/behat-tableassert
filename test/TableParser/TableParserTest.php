<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   BSD-3-Clause
 */

namespace test\Ingenerator\BehatTableAssert\TableParser;


use Behat\Gherkin\Node\TableNode;

abstract class TableParserTest extends \PHPUnit_Framework_TestCase
{

    protected function assertTableWithRows(array $expect_table_rows, TableNode $table)
    {
        $this->assertSame(
            $expect_table_rows,
            $table->getRows()
        );
    }
}
