<?php
namespace Test\Lucinda\URL\Request;

use Lucinda\UnitTest\Result;

class HeadersTest
{
    public function setIfModifiedSince()
    {
        return new Result(true, "tested via RequestTest::setHeaders");
    }
        

    public function setIfUnmodifiedSince()
    {
        return new Result(true, "tested via RequestTest::setHeaders");
    }
        

    public function setUserAgent()
    {
        return new Result(true, "tested via RequestTest::setHeaders");
    }
        

    public function setReferer()
    {
        return new Result(true, "tested via RequestTest::setHeaders");
    }
        

    public function addCookie()
    {
        return new Result(true, "tested via RequestTest::setHeaders");
    }
        

    public function addCustomHeader()
    {
        return new Result(true, "tested via RequestTest::setHeaders");
    }
}
