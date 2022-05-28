<?php

namespace Test\Lucinda\URL\Connection;

use Lucinda\UnitTest\Result;

class MultiTest
{
    public function add()
    {
        return new Result(true, "tested via MultiRequestTest::add");
    }

    public function setOption()
    {
        return new Result(true, "tested via MultiRequestTest::set");
    }


    public function setReturnTransfer()
    {
        return new Result(true, "tested via MultiRequestTest::setReturnTransfer");
    }


    public function execute()
    {
        return new Result(true, "tested via MultiRequestTest::execute");
    }
}
