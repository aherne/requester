<?php

namespace Test\Lucinda\URL\Connection;

use Lucinda\UnitTest\Result;

class SingleTest
{
    public function setOption()
    {
        return new Result(true, "tested via RequestTest::setCustomOption");
    }


    public function getOption()
    {
        return new Result(true, "tested via ResponseTest::getCustomOption");
    }


    public function execute()
    {
        return new Result(true, "tested via RequestTest::execute");
    }

    public function getDriver()
    {
        return new Result(true, "tested via Connection\MultiTest::add or Connection\SharedTest::add");
    }

    public function createFile()
    {
        return new Result(true, "tested via Request\ParametersTest::addFile");
    }
}
