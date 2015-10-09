<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   BSD-3-Clause
 */

namespace Ingenerator\BehatTableAssert;


use Behat\Gherkin\Node\TableNode;
use Ingenerator\BehatTableAssert\TableDiffer\DiffFormatter;
use Ingenerator\BehatTableAssert\TableDiffer\TableDiffer;

/**
 * Perform assertions on a pair of tables
 *
 * @package Ingenerator\BehatTableAssert
 */
class AssertTable
{

    /**
     * @var \Ingenerator\BehatTableAssert\TableDiffer\TableDiffer
     */
    protected $differ;

    /**
     * @var \Ingenerator\BehatTableAssert\TableDiffer\DiffFormatter
     */
    protected $formatter;

    /**
     * @param \Ingenerator\BehatTableAssert\TableDiffer\TableDiffer|NULL   $differ
     * @param \Ingenerator\BehatTableAssert\TableDiffer\DiffFormatter|NULL $formatter
     */
    public function __construct(TableDiffer $differ = NULL, DiffFormatter $formatter = NULL)
    {
        $this->differ    = $differ ?: new TableDiffer;
        $this->formatter = $formatter ?: new DiffFormatter;
    }

    /**
     * Assert that two tables are identical, with the same columns in the same sequence
     *
     * @param \Behat\Gherkin\Node\TableNode $expected
     * @param \Behat\Gherkin\Node\TableNode $actual
     * @param string                        $message
     *
     * @throws \Ingenerator\BehatTableAssert\TableAssertionFailureException
     * @return void
     */
    public function isSame(TableNode $expected, TableNode $actual, $message = NULL)
    {
        $this->doAssert(
            'Failed asserting that two tables were identical: ',
            [],
            $expected,
            $actual,
            $message
        );
    }

    /**
     * @param string                        $message_prefix
     * @param array                         $diff_options
     * @param \Behat\Gherkin\Node\TableNode $expected
     * @param \Behat\Gherkin\Node\TableNode $actual
     * @param string                        $message
     */
    protected function doAssert(
        $message_prefix,
        $diff_options,
        TableNode $expected,
        TableNode $actual,
        $message
    ) {
        $diff = $this->differ->diff($expected, $actual, $diff_options);
        if ($diff['different']) {
            throw new TableAssertionFailureException(
                $message_prefix.$message,
                $diff,
                $this->formatter->format($diff, $actual)
            );
        }
    }

    /**
     * Assert that two tables are equivalent ignoring column sequence
     *
     * @param \Behat\Gherkin\Node\TableNode $expected
     * @param \Behat\Gherkin\Node\TableNode $actual
     * @param string                        $message
     *
     * @throws \Ingenerator\BehatTableAssert\TableAssertionFailureException
     * @return void
     */
    public function isEqual(TableNode $expected, TableNode $actual, $message = NULL)
    {
        $this->doAssert(
            'Failed asserting that two tables were equivalent: ',
            ['ignoreColumnSequence' => TRUE],
            $expected,
            $actual,
            $message
        );
    }

    /**
     * Assert that a table contains correct values in the provided columns, ignoring any extra columns
     *
     * @param \Behat\Gherkin\Node\TableNode $expected
     * @param \Behat\Gherkin\Node\TableNode $actual
     * @param string                        $message
     *
     * @throws \Ingenerator\BehatTableAssert\TableAssertionFailureException
     * @return void
     */
    public function containsColumns(TableNode $expected, TableNode $actual, $message = NULL)
    {
        $this->doAssert(
            'Failed asserting that a table contained expected data: ',
            ['ignoreColumnSequence' => TRUE, 'ignoreExtraColumns' => TRUE],
            $expected,
            $actual,
            $message
        );

    }

    /**
     * Assert any custom comparison between two tables. Provide an array of comparator functions to
     * support custom comparisons between cell values. The callback should return true if the two
     * values match and false otherwise.
     *
     *   $tableassert->isComparable(
     *     $expected_table,
     *     $actual_table,
     *     [
     *       'comparators' => [
     *         'date' => function ($expected, $actual) { return new \DateTime($expected) === new \DateTime($actual); },
     *       ]
     *     ],
     *     'Should have same stuff',
     *   );
     *
     * @param \Behat\Gherkin\Node\TableNode $expected
     * @param \Behat\Gherkin\Node\TableNode $actual
     * @param array                         $diff_options
     * @param string                        $message
     *
     * @throws \Ingenerator\BehatTableAssert\TableAssertionFailureException
     * @return void
     */
    public function isComparable(
        TableNode $expected,
        TableNode $actual,
        array $diff_options,
        $message = NULL
    ) {
        $this->doAssert(
            'Failed comparing two tables: ',
            $diff_options,
            $expected,
            $actual,
            $message
        );
    }
}
