<?php
namespace Test\Lucinda\URL\Connection;

use Lucinda\UnitTest\Result;

class MultiTest
{
    public function add()
    {
        return new Result(true, "tested via MultiRequestTest::add");
    }
        

    public function set()
    {
        return new Result(true, "tested via MultiRequestTest::set");
    }
        

    public function execute()
    {
        return new Result(true, "tested via MultiRequestTest::execute");
    }
}
