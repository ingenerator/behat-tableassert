<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   BSD-3-Clause
 */

namespace Ingenerator\BehatTableAssert\TableParser;


use Behat\Mink\Element\NodeElement;
use Ingenerator\BehatTableAssert\TableParser\HTML\HTMLStringTableParser;
use Ingenerator\BehatTableAssert\TableParser\HTML\MinkHTMLTableParser;

/**
 * Facade / Factory class for creating TableNodes from HTML tables using the provided parsers.
 *
 * @package Ingenerator\BehatTableAssert\TableParser
 */
class HTMLTable
{
    /**
     * Direct construction of this class is not allowed - use the public named constructors to
     * create and parse CSV from the various supported data sources.
     * @internal
     */
    protected function __construct()
    {
    }

    /**
     * Parse a table from an HTML string containing the outer HTML of a <table>
     *
     * @param string $string
     *
     * @return \Ingenerator\BehatTableAssert\TableNode\PaddedTableNode
     */
    public static function fromHTMLString($string)
    {
        return self::newInstance()->makeHTMLStringTableParser()->parse($string);
    }

    /**
     * Parse a table from a Mink NodeElement wrapping a <table> object
     *
     * @param \Behat\Mink\Element\NodeElement $node
     *
     * @return \Ingenerator\BehatTableAssert\TableNode\PaddedTableNode
     * @throws \Behat\Mink\Exception\ElementHtmlException
     */
    public static function fromMinkTable(NodeElement $node)
    {
        return self::newInstance()->makeMinkHTMLTableParser()->parse($node);
    }

    /**
     * @return HTMLTable
     */
    protected static function newInstance()
    {
        return new static;
    }

    public function makeHTMLStringTableParser()
    {
        return new HTMLStringTableParser;
    }

    public function makeMinkHTMLTableParser()
    {
        return new MinkHTMLTableParser($this->makeHTMLStringTableParser());
    }
}
