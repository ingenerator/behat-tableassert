<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   BSD-3-Clause
 */

namespace Ingenerator\BehatTableAssert\TableParser\CSV;


use Behat\Gherkin\Node\TableNode;
use Ingenerator\BehatTableAssert\TableNode\PaddedTableNode;

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

        $original_position = \ftell($stream);
        try {
            \fseek($stream, 0);

            return new PaddedTableNode($this->readCSVRows($stream));

        } finally {
            \fseek($stream, $original_position);
        }
    }

    /**
     * @param $string
     *
     * @return bool
     */
    protected function isValidStream($string)
    {
        $valid = \is_resource($string) && \get_resource_type($string) === 'stream';

        return $valid;
    }

    /**
     * @param resource $stream
     *
     * @return array
     */
    protected function readCSVRows($stream)
    {
        $rows = [];
        while ($row = \fgetcsv($stream)) {
            if ($row === [NULL]) {
                $row = [];
            }
            $rows[] = $row;
        }

        if ( ! \array_filter($rows)) {
            throw new \InvalidArgumentException('The provided CSV was empty');
        }

        return $rows;
    }
}
