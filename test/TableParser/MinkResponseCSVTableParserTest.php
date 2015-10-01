<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   BSD-3-Clause
 */

namespace test\Ingenerator\BehatTableAssert\TableParser;


use Behat\Mink\Driver\CoreDriver;
use Behat\Mink\Session;
use Ingenerator\BehatTableAssert\TableParser\CSVStringTableParser;
use Ingenerator\BehatTableAssert\TableParser\MinkResponseCSVTableParser;
use test\mock\Ingenerator\BehatTableAssert\TableParser\MockCSVStreamTableParser;

class MinkResponseCSVTableParserTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var MockCSVStreamTableParser
     */
    protected $stream_parser;

    public function test_it_is_initialisable()
    {
        $this->assertInstanceOf(
            'Ingenerator\BehatTableAssert\TableParser\MinkResponseCSVTableParser',
            $this->newSubject()
        );
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function test_it_throws_if_driver_is_not_started()
    {
        $this->newSubject()->parse(new ArrayMinkSessionStub(['is_started' => FALSE]));
    }

    /**
     * @expectedException \Behat\Mink\Exception\ExpectationException
     * @testWith [{}]
     *           [{"Content-Type": "text/html"}]
     */
    public function test_it_throws_if_content_type_is_not_csv($headers)
    {
        $this->newSubject()->parse(
            new ArrayMinkSessionStub(['headers' => $headers])
        );
    }

    /**
     * @expectedException \Behat\Mink\Exception\ResponseTextException
     * @testWith ["<!DOCTYPE html>"]
     *           ["<!doctype html>"]
     *           ["<!DOCTYPE html public \"-//W3C//DTD HTML 4.01//EN\">"]
     *           ["<html></html>"]
     *           ["<html></html> "]
     *           ["<html></html>\n"]
     *           ["<html></html> \n\n"]
     */
    public function test_it_throws_if_content_looks_like_html($response_text)
    {
        $this->newSubject()->parse(
            new ArrayMinkSessionStub(
                [
                    'headers' => ['Content-Type' => 'text/csv'],
                    'content' => $response_text
                ]
            )
        );
    }

    /**
     * @testWith [{"Content-Type": "text/csv"}, "1,2,3"]
     *           [{"Content-Type": "text/csv;charset=utf-8"}, "1,2,3"]
     *           [{"Content-Type": "text/csv"}, "1,2,3,\"<html>html inside file</html>\""]
     */
    public function test_it_parses_csv_response_content_to_table($headers, $content)
    {
        $table = $this->newSubject()->parse(
            new ArrayMinkSessionStub(
                [
                    'headers' => $headers,
                    'content' => $content
                ]
            )
        );
        $this->assertSame($table, $this->stream_parser->getMockedTable());
        $this->assertEquals($content, $this->stream_parser->getParsedString());
    }

    public function setUp()
    {
        parent::setUp();
        $this->stream_parser = new MockCSVStreamTableParser;
    }

    protected function newSubject()
    {
        return new MinkResponseCSVTableParser(
            new CSVStringTableParser($this->stream_parser)
        );
    }
}

class ArrayMinkSessionStub extends Session
{
    public function __construct(array $options = [])
    {
        parent::__construct(new ArrayMinkDriverStub($options));
    }

}

class ArrayMinkDriverStub extends CoreDriver
{
    protected $options = [
        'content'    => '',
        'is_started' => TRUE,
        'headers'    => [
        ]
    ];

    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options, $options);
    }

    public function setSession(Session $session)
    {

    }

    public function isStarted()
    {
        return $this->options['is_started'];
    }

    public function getResponseHeaders()
    {
        return $this->options['headers'];
    }

    public function getContent()
    {
        return $this->options['content'];
    }

}
