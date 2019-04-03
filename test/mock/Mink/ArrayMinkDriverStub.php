<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   BSD-3-Clause
 */
namespace test\mock\Ingenerator\BehatTableAssert\Mink;

use Behat\Mink\Driver\CoreDriver;
use Behat\Mink\Session;

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
        $this->options = \array_merge($this->options, $options);
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
