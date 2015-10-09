<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   BSD-3-Clause
 */

namespace Ingenerator\BehatTableAssert\TableParser\HTML;


use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementHtmlException;

/**
 * Parse an HTML table from a NodeElement representing the <table> element into a TableNode
 *
 * @package Ingenerator\BehatTableAssert\TableParser\HTML
 */
class MinkHTMLTableParser
{
    /**
     * @var HTMLStringTableParser
     */
    protected $html_parser;

    /**
     * MinkHTMLTableParser constructor.
     *
     * @param HTMLStringTableParser $html_parser
     */
    public function __construct(HTMLStringTableParser $html_parser)
    {
        $this->html_parser = $html_parser;
    }

    /**
     * @param \Behat\Mink\Element\NodeElement $html_table
     *
     * @return \Ingenerator\BehatTableAssert\TableNode\PaddedTableNode
     * @throws \Behat\Mink\Exception\ElementHtmlException
     * @throws \InvalidArgumentException
     */
    public function parse(NodeElement $html_table)
    {
        $tag = $html_table->getTagName();
        if ($tag !== 'table') {
            throw new ElementHtmlException(
                'Expected a <table> node but got <'.$tag.'>',
                $html_table->getSession()->getDriver(),
                $html_table
            );
        }

        return $this->html_parser->parse($html_table->getOuterHtml());
    }
}
