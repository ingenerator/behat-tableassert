<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   BSD-3-Clause
 */

namespace Ingenerator\BehatTableAssert\TableParser;


use Ingenerator\BehatTableAssert\TableParser\CSV\CSVStreamTableParser;
use Ingenerator\BehatTableAssert\TableParser\CSV\CSVStringTableParser;
use Ingenerator\BehatTableAssert\TableParser\CSV\MinkResponseCSVTableParser;

/**
 * Facade/factory class for creating TableNodes from CSV data using the provided parsers
 *
 * @package Ingenerator\BehatTableAssert\TableParser
 */
class CSVTable
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
     * Parse CSV from any arbitrary stream resource
     *
     * @param resource $stream
     *
     * @return \Behat\Gherkin\Node\TableNode
     * @throws \InvalidArgumentException
     */
    public static function fromStream($stream)
    {
        return self::newInstance()->makeCSVStreamTableParser()->parse($stream);
    }

    /**
     * Parse CSV from any string
     *
     * @param string $string
     *
     * @return \Behat\Gherkin\Node\TableNode
     * @throws \InvalidArgumentException
     */
    public static function fromString($string)
    {
        return self::newInstance()->makeCSVStringTableParser()->parse($string);
    }

    /**
     * Parse CSV from the HTTP response received by Mink on the most recent request
     *
     * @param \Behat\Mink\Session $session
     *
     * @return \Behat\Gherkin\Node\TableNode
     * @throws \Behat\Mink\Exception\ExpectationException if the Content-Type is not expected
     * @throws \Behat\Mink\Exception\ResponseTextException if the response text looks like HTML
     * @throws \InvalidArgumentException
     */
    public static function fromMinkResponse(\Behat\Mink\Session $session)
    {
        return self::newInstance()->makeMinkResponseCSVTableParser()->parse($session);
    }

    /**
     * @return CSVTable
     */
    protected static function newInstance()
    {
        return new static;
    }

    /**
     * @return \Ingenerator\BehatTableAssert\TableParser\CSV\CSVStreamTableParser
     */
    public function makeCSVStreamTableParser()
    {
        return new CSVStreamTableParser;
    }

    /**
     * @return \Ingenerator\BehatTableAssert\TableParser\CSV\CSVStringTableParser
     */
    public function makeCSVStringTableParser()
    {
        return new CSVStringTableParser(
            $this->makeCSVStreamTableParser()
        );
    }

    /**
     * @return \Ingenerator\BehatTableAssert\TableParser\CSV\MinkResponseCSVTableParser
     */
    public function makeMinkResponseCSVTableParser()
    {
        return new MinkResponseCSVTableParser(
            $this->makeCSVStringTableParser()
        );
    }
}
