<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   BSD-3-Clause
 */
namespace test\mock\Ingenerator\BehatTableAssert\TableParser;

use Behat\Gherkin\Node\TableNode;
use Ingenerator\BehatTableAssert\TableNode\PaddedTableNode;
use Ingenerator\BehatTableAssert\TableParser\HTML\HTMLStringTableParser;

class MockHTMLStringTableParser extends HTMLStringTableParser
{
    /**
     * @var \Behat\Gherkin\Node\TableNode
     */
    protected $table;

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
        $this->table = new PaddedTableNode([['stuff']]);
    }

    public function parse($string)
    {
        if ($this->parsed) {
            throw new \BadMethodCallException('Multiple calls to '.__METHOD__.' were not expected');
        }

        $this->parsed        = TRUE;
        $this->parsed_string = $string;

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
