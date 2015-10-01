<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   BSD-3-Clause
 */
namespace test\mock\Ingenerator\BehatTableAssert\TableParser;

class ThrowingCSVStreamTableParserStub extends MockCSVStreamTableParser
{
    public function parse($stream)
    {
        parent::parse($stream);
        throw new \InvalidArgumentException(__CLASS__.' always throws for parse');
    }

}
