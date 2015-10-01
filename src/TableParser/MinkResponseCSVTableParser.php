<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   BSD-3-Clause
 */

namespace Ingenerator\BehatTableAssert\TableParser;


use Behat\Mink\Exception\ExpectationException;
use Behat\Mink\Exception\ResponseTextException;
use Behat\Mink\Session;

/**
 * Parse a CSV table from a Mink response - eg after navigating to a CSV URL.
 *
 * [!!] Note that if your server sends a Content-Disposition=attachment header then this parser will
 *      only work if the mink driver ignores it and renders the response directly. For example, it
 *      will work with Goutte and BrowserKit based drivers. Real browser-based drivers (Selenium,
 *      Zombie, etc) are likely to save the file to disk instead and you will need to locate it and
 *      use one of the other parsers to read it.
 *
 * @package Ingenerator\BehatTableAssert\TableParser
 */
class MinkResponseCSVTableParser
{
    /**
     * @var CSVStringTableParser
     */
    protected $csv_parser;

    /**
     * @param CSVStringTableParser $csv_parser
     */
    public function __construct(CSVStringTableParser $csv_parser)
    {
        $this->csv_parser = $csv_parser;
    }

    /**
     * @param \Behat\Mink\Session $session
     *
     * @return \Behat\Gherkin\Node\TableNode
     * @throws \Behat\Mink\Exception\ExpectationException
     * @throws \Behat\Mink\Exception\ResponseTextException
     */
    public function parse(Session $session)
    {
        if ( ! $session->isStarted()) {
            throw new \BadMethodCallException(
                'The Mink session must be started before '.__METHOD__
            );
        }

        $content_type = $session->getResponseHeader('Content-Type');
        if ($this->isCSVContentType($content_type)) {
            throw new ExpectationException(
                'Expected Content-Type of "text/csv" but got '.$content_type, $session->getDriver()
            );
        }

        $response_text = $session->getDriver()->getContent();
        if ($this->isLikeHTML($response_text)) {
            throw new ResponseTextException(
                'Response text content looks like HTML but CSV data was expected',
                $session->getDriver()
            );
        }

        return $this->csv_parser->parse($response_text);
    }

    /**
     * @param string $response_text
     *
     * @return bool
     */
    protected function isLikeHTML($response_text)
    {
        return (preg_match('_^<!DOCTYPE html_i', $response_text) || preg_match(
                '_</html>\s*$_',
                $response_text
            ));

    }

    /**
     * @param string $content_type
     *
     * @return bool
     */
    protected function isCSVContentType($content_type)
    {
        list($content_type, $metadata) = explode(';', $content_type.';', 2);

        return $content_type !== 'text/csv';
    }
}
