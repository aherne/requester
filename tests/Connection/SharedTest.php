<?php

namespace Test\Lucinda\URL\Connection;

use Lucinda\UnitTest\Result;

class SharedTest
{
    public function setOption()
    {
        return new Result(true, "tested via SharedRequestTest::__construct");
    }


    public function add()
    {
        return new Result(true, "tested via SharedRequestTest::add");
    }
}
