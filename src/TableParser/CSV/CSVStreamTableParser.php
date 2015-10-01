<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   BSD-3-Clause
 */

namespace Ingenerator\BehatTableAssert\TableParser\CSV;


use Behat\Gherkin\Node\TableNode;

/**
 * Parses a stream containing CSV content into a TableNode
 *
 * @package Ingenerator\BehatTableAssert\TableParser
 */
class CSVStreamTableParser
{

    /**
     * Parse CSV content from a stream into a TableNode
     *
     * @param resource $stream
     *
     * @return \Behat\Gherkin\Node\TableNode
     * @throws \InvalidArgumentException
     */
    public function parse($stream)
    {
        if ( ! ($this->isValidStream($stream))) {
            throw new \InvalidArgumentException(__METHOD__.' requires a valid stream resource');
        }

        $original_position = ftell($stream);
        try {
            fseek($stream, 0);

            return $this->createTableFromRows($this->readCSVRows($stream));

        } finally {
            fseek($stream, $original_position);
        }
    }

    /**
     * @param $string
     *
     * @return bool
     */
    protected function isValidStream($string)
    {
        $valid = is_resource($string) && get_resource_type($string) === 'stream';

        return $valid;
    }

    /**
     * @param $rows
     *
     * @return \Behat\Gherkin\Node\TableNode
     */
    protected function createTableFromRows($rows)
    {
        $column_count = max(array_map('count', $rows));
        $table        = new TableNode;
        foreach ($rows as $row) {
            $table->addRow(array_pad($row, $column_count, '{empty}'));
        }

        return $table;
    }

    /**
     * @param resource $stream
     *
     * @return array
     */
    protected function readCSVRows($stream)
    {
        $rows = [];
        while ($row = fgetcsv($stream)) {
            if ($row === [NULL]) {
                $row = [];
            }
            $rows[] = $row;
        }

        if ( ! array_filter($rows)) {
            throw new \InvalidArgumentException('The provided CSV was empty');
        }

        return $rows;
    }
}
