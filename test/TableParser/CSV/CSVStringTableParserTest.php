<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   BSD-3-Clause
 */
namespace test\Ingenerator\BehatTableAssert\TableParser\CSV;

use test\mock\Ingenerator\BehatTableAssert\TableParser\MockCSVStreamTableParser;
use test\mock\Ingenerator\BehatTableAssert\TableParser\ThrowingCSVStreamTableParserStub;

class CSVStringTableParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \test\mock\Ingenerator\BehatTableAssert\TableParser\MockCSVStreamTableParser
     */
    protected $stream_parser;

    public function test_it_is_initialisable()
    {
        $this->assertInstanceOf(
            'Ingenerator\BehatTableAssert\TableParser\CSV\CSVStringTableParser',
            $this->newSubject()
        );
    }

    protected function newSubject()
    {
        return new \Ingenerator\BehatTableAssert\TableParser\CSV\CSVStringTableParser(
            $this->stream_parser
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_it_throws_if_asked_to_parse_non_string()
    {
        $this->newSubject()->parse(1);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_it_throws_if_asked_to_parse_object_without_to_string_method()
    {
        $this->newSubject()->parse(new \stdClass);
    }

    public function test_it_parses_string_using_temporary_stream()
    {
        $csv = "1,2\n3,4";
        $this->newSubject()->parse($csv);
        $this->assertEquals($csv, $this->stream_parser->getParsedString());
    }

    public function test_it_supports_parsing_objects_with_to_string_method()
    {
        $this->newSubject()->parse(new AnythingToStringStub);
        $this->assertEquals(
            AnythingToStringStub::TO_STRING,
            $this->stream_parser->getParsedString()
        );
    }

    public function test_it_closes_temporary_stream_after_parsing()
    {
        $this->newSubject()->parse('12345');
        $this->assertTrue($this->stream_parser->isParsedStreamClosed());
    }

    public function test_it_closes_temporary_stream_on_parser_exception()
    {
        $this->stream_parser = new ThrowingCSVStreamTableParserStub;
        try {
            $this->newSubject()->parse('stuff');
        } catch (\InvalidArgumentException $e) {
            // Expected
        }

        $this->assertTrue($this->stream_parser->isParsedStreamClosed());
    }

    public function test_it_returns_table_from_stream_parser()
    {
        $this->assertSame(
            $this->stream_parser->getMockedTable(),
            $this->newSubject()->parse('stuff')
        );
    }

    public function setUp()
    {
        parent::setUp();
        $this->stream_parser = new MockCSVStreamTableParser;
    }
}


class AnythingToStringStub
{
    const TO_STRING = 'AnythingToStringStub';

    public function __toString()
    {
        return self::TO_STRING;
    }
}
