<?php

namespace Test\Lucinda\URL;

use Lucinda\URL\SharedRequest;
use Lucinda\URL\Request;
use Lucinda\UnitTest\Result;
use Lucinda\URL\Cookies;
use Lucinda\URL\Cookies\CookieFile;

class SharedRequestTest
{
    public function add()
    {
        $sharedRequest = new SharedRequest();

        $request1 = new Request(RECEIVER_HTTP);
        $sharedRequest->add($request1);
        $cookies1 = new Cookies($request1->getConnection());
        $request1->execute();

        $request2 = new Request(RECEIVER_HTTP);
        $sharedRequest->add($request2);
        $cookies2 = new Cookies($request2->getConnection());
        $request2->execute();

//        $cookieFile = new CookieFile();
//        return new Result($cookieFile->encrypt($cookies1->getAll()[0]) == $cookieFile->encrypt($cookies2->getAll()[0]));
    }
}
