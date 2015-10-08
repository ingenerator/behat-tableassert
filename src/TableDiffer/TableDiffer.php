<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   BSD-3-Clause
 */

namespace Ingenerator\BehatTableAssert\TableDiffer;

use Behat\Gherkin\Node\TableNode;

/**
 * Compares two TableNodes and produces a list of differences. Optionally supports ignoring
 * additional columns, ignoring extra columns, and using custom callback functions to compare
 * cell values.
 */
class TableDiffer
{
    /**
     * @var array
     */
    protected $default_options = [
        'comparators'          => [],
        'ignoreColumnSequence' => FALSE,
        'ignoreExtraColumns'   => FALSE
    ];

    /**
     * @var array
     */
    protected $options;

    /**
     * Compare two table nodes and return an array of the differences between them
     *
     * @param \Behat\Gherkin\Node\TableNode $expected
     * @param \Behat\Gherkin\Node\TableNode $actual
     * @param array                         $options
     *
     * @return array of differences
     */
    public function diff(TableNode $expected, TableNode $actual, array $options = [])
    {
        $this->options = $this->validateOptions($options, $expected);

        if ($diff = $this->diffColumnStructure($expected->getRow(0), $actual->getRow(0))) {
            return $diff;
        }

        return $this->diffValues($expected->getHash(), $actual->getHash());
    }

    /**
     * @param array                         $options
     * @param \Behat\Gherkin\Node\TableNode $expected
     *
     * @return array
     */
    protected function validateOptions(array $options, TableNode $expected)
    {
        if ($unknown_options = array_diff(
            array_keys($options),
            array_keys($this->default_options)
        )
        ) {
            throw new \InvalidArgumentException(
                'Unexpected options: '.implode(', ', $options)
            );
        }

        $options = array_merge($this->default_options, $options);

        $this->validateComparatorOptions($options['comparators'], $expected->getRow(0));

        return $options;
    }

    /**
     * @param callable[] $comparators
     * @param string[]   $expected_columns
     */
    protected function validateComparatorOptions(array $comparators, array $expected_columns)
    {
        foreach ($comparators as $column => $comparator) {
            if ( ! in_array($column, $expected_columns)) {
                throw new \InvalidArgumentException(
                    'Cannot register custom comparator for unexpected column '.$column
                );
            }

            if ( ! is_callable($comparator)) {
                throw new \InvalidArgumentException(
                    'Custom comparator for column '.$column.' was not callable'
                );
            }
        }
    }

    /**
     * @param string[] $expected_columns
     * @param string[] $actual_columns
     *
     * @return array of differences
     */
    protected function diffColumnStructure(array $expected_columns, array $actual_columns)
    {
        $diff            = [];
        $missing_columns = array_diff($expected_columns, $actual_columns);
        $extra_columns   = array_diff($actual_columns, $expected_columns);

        if ($missing_columns OR $extra_columns) {
            if ($missing_columns) {
                $diff[] = [
                    'type'    => 'structural',
                    'message' => sprintf(
                        "Missing columns: '%s' (got '%s')",
                        implode("', '", $missing_columns),
                        implode("', '", $actual_columns)
                    )
                ];
            }

            if ($extra_columns AND ! $this->options['ignoreExtraColumns']) {
                $diff[] = [
                    'type'    => 'structural',
                    'message' => sprintf(
                        "Unexpected columns: '%s'",
                        implode("', '", $extra_columns)
                    )
                ];
            }
        } elseif (($actual_columns != $expected_columns) AND ! $this->options['ignoreColumnSequence']) {
            $diff[] = [
                'type'    => 'structural',
                'message' => sprintf(
                    "Unexpected column sequence:\n - Expected: '%s'\n - Got:      '%s'",
                    implode("', '", $expected_columns),
                    implode("', '", $actual_columns)
                )
            ];
        }

        return $diff;
    }

    /**
     * @param string[] $expected_hash
     * @param string[] $actual_hash
     *
     * @return array of differences
     */
    protected function diffValues(array $expected_hash, array $actual_hash)
    {
        $diff      = [];
        $row_index = 0;

        foreach ($expected_hash as $row_index => $expected_cells) {
            if ( ! isset($actual_hash[$row_index])) {
                $diff[] = [
                    'type'    => 'structural',
                    'message' => sprintf(
                        'Missing row #%d (expected: %s)',
                        $row_index + 1,
                        implode(', ', $expected_cells)
                    )
                ];
                continue;
            }
            $diff = array_merge(
                $diff,
                $this->diffRow(
                    $expected_cells,
                    $actual_hash[$row_index],
                    $row_index
                )
            );
        }

        for ($row_index = $row_index + 1; $row_index < count($actual_hash); $row_index++) {
            $diff[] = [
                'type'    => 'structural',
                'message' => sprintf(
                    'Additional row #%d (got: %s)',
                    $row_index + 1,
                    implode(', ', $actual_hash[$row_index])
                )
            ];
        }

        return $diff;
    }

    /**
     * @param $expected_cells
     * @param $actual_cells
     * @param $row_index
     *
     * @return array
     */
    protected function diffRow($expected_cells, $actual_cells, $row_index)
    {
        $diff = [];
        foreach ($expected_cells as $column => $expected_value) {
            $actual_value = $actual_cells[$column];

            if ( ! ($this->isEquivalent($column, $expected_value, $actual_value))) {
                $diff[] = [
                    'type'   => 'value',
                    'row'    => $row_index + 1,
                    'col'    => $column,
                    'expect' => $expected_value,
                    'actual' => $actual_value
                ];
            }
        }

        return $diff;
    }

    /**
     * @param string $column
     * @param string $expected_value
     * @param string $actual_value
     *
     * @return bool
     */
    protected function isEquivalent($column, $expected_value, $actual_value)
    {
        if (isset($this->options['comparators'][$column])) {
            return call_user_func(
                $this->options['comparators'][$column],
                $expected_value,
                $actual_value
            );
        } else {
            return $expected_value === $actual_value;
        }
    }
}
