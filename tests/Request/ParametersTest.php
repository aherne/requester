<?php

namespace Test\Lucinda\URL\Request;

use Lucinda\URL\Request;
use Lucinda\URL\Request\Method;
use Lucinda\UnitTest\Result;

class ParametersTest
{
    public function add()
    {
        $request = new Request(RECEIVER_HTTP);
        $request->setMethod(Method::POST);
        $parameters = $request->setParameters();
        $parameters->add("a", "b");
        $parameters->add("c", "d");
        $response = $request->execute();
        $payload = json_decode($response->getBody(), true);
        return new Result($payload["request"] == ["a"=>"b", "c"=>"d"]);
    }


    public function addFile()
    {
        $request = new Request(RECEIVER_HTTP);
        $request->setMethod(Method::POST);
        $parameters = $request->setParameters();
        $parameters->add("a", "b");
        $parameters->addFile("c", dirname(__DIR__, 2).DIRECTORY_SEPARATOR."composer.json", "composer");
        $response = $request->execute();
        $payload = json_decode($response->getBody(), true);
        return new Result(isset($payload["files"]["c"]["name"]) && $payload["files"]["c"]["name"] == "composer");
    }
}
