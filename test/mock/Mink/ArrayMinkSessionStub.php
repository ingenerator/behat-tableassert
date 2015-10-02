<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   BSD-3-Clause
 */
namespace test\mock\Ingenerator\BehatTableAssert\Mink;

use Behat\Mink\Session;

class ArrayMinkSessionStub extends Session
{
    public function __construct(array $options = [])
    {
        parent::__construct(new ArrayMinkDriverStub($options));
    }

}
