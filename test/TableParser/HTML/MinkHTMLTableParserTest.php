<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   BSD-3-Clause
 */

namespace test\Ingenerator\BehatTableAssert\TableParser\HTML;


use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementHtmlException;
use Behat\Mink\Session;
use Ingenerator\BehatTableAssert\TableParser\HTML\MinkHTMLTableParser;
use test\mock\Ingenerator\BehatTableAssert\Mink\ArrayMinkSessionStub;
use test\mock\Ingenerator\BehatTableAssert\TableParser\MockHTMLStringTableParser;

class MinkHTMLTableParserTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var MockHTMLStringTableParser
     */
    protected $html_parser;

    public function test_it_is_initialisable()
    {
        $this->assertInstanceOf(
            'Ingenerator\BehatTableAssert\TableParser\HTML\MinkHTMLTableParser',
            $this->newSubject()
        );
    }

    public function test_it_throws_when_parsing_node_that_is_not_a_table()
    {
        $this->expectException(ElementHtmlException::class);
        $this->expectExceptionMessage('Expected a <table>');

        $this->newSubject()->parse(new StringNodeElementStub('<div></div>'));
    }

    public function test_it_parses_node_outer_html_with_html_string_parser()
    {
        $html  = '<table><thead><tr><td>Stuff</td></tr></thead><tbody></tbody></table>';
        $table = $this->newSubject()->parse(new StringNodeElementStub($html));
        $this->assertSame($html, $this->html_parser->getParsedString());
        $this->assertSame($table, $this->html_parser->getMockedTable());
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->html_parser = new MockHTMLStringTableParser;
    }

    protected function newSubject()
    {
        return new MinkHTMLTableParser(
            $this->html_parser
        );
    }

}

class StringNodeElementStub extends NodeElement
{
    protected $html;

    public function __construct($html)
    {
        $this->html = $html;
        parent::__construct('anything', new ArrayMinkSessionStub());
    }

    public function getTagName()
    {
        \preg_match('_^<([^>]+)>_', $this->html, $matches);

        return $matches[1];
    }

    /**
     * Returns element outer html.
     *
     * @return string
     */
    public function getOuterHtml()
    {
        return $this->html;
    }


}
