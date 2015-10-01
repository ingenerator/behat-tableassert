<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   BSD-3-Clause
 */

namespace Ingenerator\BehatTableAssert\TableParser\CSV;

/**
 * Parses a CSV string into a TableNode
 *
 * @package Ingenerator\BehatTableAssert\TableParser
 */
class CSVStringTableParser
{
    /**
     * @var CSVStreamTableParser
     */
    protected $stream_parser;

    /**
     * @param CSVStreamTableParser $stream_parser
     */
    public function __construct(CSVStreamTableParser $stream_parser)
    {
        $this->stream_parser = $stream_parser;
    }

    /**
     * @param string $string
     *
     * @return \Behat\Gherkin\Node\TableNode
     * @throws \InvalidArgumentException
     */
    public function parse($string)
    {
        if ( ! $this->isStringLike($string)) {
            throw new \InvalidArgumentException(
                __METHOD__.' expects to receive a string-like argument'
            );
        }

        $stream = fopen('php://memory', 'w');
        try {
            fwrite($stream, $string);

            return $this->stream_parser->parse($stream);
        } finally {
            fclose($stream);
        }
    }

    /**
     * @param mixed $var
     *
     * @return bool
     */
    protected function isStringLike($var)
    {
        if (is_string($var)) {
            return TRUE;
        }

        if (is_object($var) AND method_exists($var, '__toString')) {
            return TRUE;
        }

        return FALSE;
    }
}
