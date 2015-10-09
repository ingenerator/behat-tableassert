<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   BSD-3-Clause
 */

namespace Ingenerator\BehatTableAssert;

/**
 * @package Ingenerator\BehatTableAssert
 */
class TableAssertionFailureException extends \RuntimeException
{
    /**
     * @var array
     */
    protected $diff;

    /**
     * @var string
     */
    protected $diff_string;

    /**
     * @param string $message
     * @param array  $diff
     * @param string $diff_string
     */
    public function __construct($message, array $diff, $diff_string)
    {
        $this->diff        = $diff;
        $this->diff_string = $diff_string;
        parent::__construct($message);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getMessage()."\n\n".$this->diff_string;
    }

    /**
     * @return array
     */
    public function getDiff()
    {
        return $this->diff;
    }

    /**
     * @return string
     */
    public function getDiffString()
    {
        return $this->diff_string;
    }

}
