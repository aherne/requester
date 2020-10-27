<?php
namespace Test\Lucinda\URL;
    
use Lucinda\URL\SharedRequest;
use Lucinda\URL\Request;
use Lucinda\UnitTest\Result;

class SharedRequestTest
{
    public function add()
    {
        $sharedRequest = new SharedRequest();
        
        $request1 = new Request(RECEIVER_HTTP);
        $sharedRequest->add($request1);
        $response1 = $request1->execute();
        
        $request2 = new Request(RECEIVER_HTTP);
        $sharedRequest->add($request2);
        $response2 = $request2->execute();
        
        return new Result($response1->getCookies()[0]->toString() == $response2->getCookies()[0]->toString());
    }
}
