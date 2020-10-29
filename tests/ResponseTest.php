<?php
namespace Test\Lucinda\URL;
    
use Lucinda\URL\Request;
use Lucinda\UnitTest\Result;
use Lucinda\URL\Cookie;

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
        

    public function getCookies()
    {
        $file = dirname(__DIR__).DIRECTORY_SEPARATOR."cookies.txt";
        
        file_put_contents($file, "");
        
        $request = new Request(RECEIVER_HTTP);
        $cookies = $request->setCookies();
        $cookies->write(new Cookie("test", "me"));
        $cookies->setFileToRead($file);
        $cookies->setFileToWrite($file);
        $response = $request->execute();
        $cookies->flushAll();
        
        unlink($file);
                
        return new Result(!empty($response->getCookies()));
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
        return new Result(!empty($headers["set-cookie"]) && strpos($headers["set-cookie"], "PHPSESSID=")===0);
    }
}
