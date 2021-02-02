<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   BSD-3-Clause
 */
namespace test\Ingenerator\BehatTableAssert\TableParser\CSV;

use Behat\Mink\Driver\CoreDriver;
use Behat\Mink\Exception\ExpectationException;
use Behat\Mink\Exception\ResponseTextException;
use Behat\Mink\Session;
use Ingenerator\BehatTableAssert\TableParser\CSV\CSVStringTableParser;
use Ingenerator\BehatTableAssert\TableParser\CSV\MinkResponseCSVTableParser;
use test\mock\Ingenerator\BehatTableAssert\Mink\ArrayMinkSessionStub;
use test\mock\Ingenerator\BehatTableAssert\TableParser\MockCSVStreamTableParser;

class MinkResponseCSVTableParserTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var MockCSVStreamTableParser
     */
    protected $stream_parser;

    public function test_it_is_initialisable()
    {
        $this->assertInstanceOf(
            'Ingenerator\BehatTableAssert\TableParser\CSV\MinkResponseCSVTableParser',
            $this->newSubject()
        );
    }

    public function test_it_throws_if_driver_is_not_started()
    {
        $this->expectException(\BadMethodCallException::class);

        $this->newSubject()->parse(new ArrayMinkSessionStub(['is_started' => FALSE]));
    }

    /**
     * @testWith [{}]
     *           [{"Content-Type": "text/html"}]
     */
    public function test_it_throws_if_content_type_is_not_csv($headers)
    {
        $this->expectException(ExpectationException::class);

        $this->newSubject()->parse(
            new ArrayMinkSessionStub(['headers' => $headers])
        );
    }

    /**
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
        $this->expectException(ResponseTextException::class);

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

    public function setUp(): void
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
