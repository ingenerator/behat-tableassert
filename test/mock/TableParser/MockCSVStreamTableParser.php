<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   BSD-3-Clause
 */
namespace test\mock\Ingenerator\BehatTableAssert\TableParser;

use Behat\Gherkin\Node\TableNode;
use Ingenerator\BehatTableAssert\TableParser\CSVStreamTableParser;

class MockCSVStreamTableParser extends CSVStreamTableParser
{
    /**
     * @var \Behat\Gherkin\Node\TableNode
     */
    protected $table;

    /**
     * @var resource
     */
    protected $parsed_stream;

    /**
     * @var string
     */
    protected $parsed_string;

    /**
     * @var bool
     */
    protected $parsed = FALSE;

    public function __construct()
    {
        $this->table = new TableNode;
    }

    /**
     * Parse CSV content from a stream into a TableNode
     *
     * @param resource $stream
     *
     * @return \Behat\Gherkin\Node\TableNode
     */
    public function parse($stream)
    {
        if ($this->parsed) {
            throw new \BadMethodCallException('Multiple calls to '.__METHOD__.' were not expected');
        }

        $this->parsed = TRUE;
        $this->parsed_stream = $stream;
        $this->parsed_string = stream_get_contents($stream, -1, 0);

        return $this->table;
    }

    /**
     * @return \Behat\Gherkin\Node\TableNode
     */
    public function getMockedTable()
    {
        return $this->table;
    }

    /**
     * @return bool
     */
    public function isParsedStreamClosed()
    {
        $this->throwIfNotParsed(__METHOD__);

        return ( ! is_resource($this->parsed_stream));
    }

    /**
     * @return string
     */
    public function getParsedString()
    {
        $this->throwIfNotParsed(__METHOD__);

        return $this->parsed_string;
    }

    /**
     * @param string $method
     */
    protected function throwIfNotParsed($method)
    {
        if ( ! $this->parsed) {
            throw new \BadMethodCallException(
                'Cannot call '.$method.' before any parsing has been done'
            );
        }
    }

}
