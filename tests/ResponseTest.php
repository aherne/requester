<?php
namespace Test\Lucinda\URL;

use Lucinda\URL\Request;
use Lucinda\UnitTest\Result;

class ResponseTest
{
    private $object;
    
    public function __construct()
    {
        $request = new Request(RECEIVER_HTTP);
        $this->object = $request->execute();
    }

    public function getCustomOption()
    {
        return new Result($this->object->getCustomOption(CURLINFO_NAMELOOKUP_TIME) > 0);
    }
        

    public function getDuration()
    {
        return new Result($this->object->getDuration() > 0);
    }
        

    public function getStatusCode()
    {
        return new Result($this->object->getStatusCode() == 200);
    }
        

    public function getURL()
    {
        return new Result($this->object->getURL() == RECEIVER_HTTP);
    }
        

    public function getBody()
    {
        $response = json_decode($this->object->getBody(), true);
        return new Result($response["body"] == "OK");
    }

    public function getHeaders()
    {
        $headers = $this->object->getHeaders();
        return new Result(!empty($headers["Set-Cookie"]) && strpos($headers["Set-Cookie"], "PHPSESSID=")===0);
    }
}
