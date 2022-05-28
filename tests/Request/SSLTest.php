<?php

namespace Test\Lucinda\URL\Request;

use Lucinda\URL\Request;
use Lucinda\UnitTest\Result;

class SSLTest
{
    public function setCertificate()
    {
        $request = new Request(RECEIVER_HTTPS);
        $request->setSSL(dirname(__DIR__, 2).DIRECTORY_SEPARATOR."certificates".DIRECTORY_SEPARATOR."certificate.crt");
        $response = $request->execute();
        return new Result(json_decode($response->getBody(), true)["body"]=="OK");
    }


    public function setPrivateKey()
    {
        $request = new Request(RECEIVER_HTTPS);
        $ssl = $request->setSSL(dirname(__DIR__, 2).DIRECTORY_SEPARATOR."certificates".DIRECTORY_SEPARATOR."certificate.crt");
        $ssl->setPrivateKey(dirname(__DIR__, 2).DIRECTORY_SEPARATOR."certificates".DIRECTORY_SEPARATOR."private.key", "test");
        $response = $request->execute();
        return new Result(json_decode($response->getBody(), true)["body"]=="OK");
    }
}
