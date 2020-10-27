<?php
namespace Test\Lucinda\URL\Request;

use Lucinda\URL\Request;
use Lucinda\UnitTest\Result;
use Lucinda\URL\Cookie;

class CookiesTest
{
    private $file;
    
    public function __construct()
    {
        $this->file = dirname(__DIR__).DIRECTORY_SEPARATOR."cookies.txt";
    }
    
    public function startNewSession()
    {
    }
    
    
    public function setFileToRead()
    {
        return new Result(true, "tested via write");
    }
    
    
    public function setFileToWrite()
    {
        return new Result(true, "tested via write");
    }
    
    
    public function write()
    {
        file_put_contents($this->file, "");
        
        $request = new Request(RECEIVER_HTTP);
        $cookies = $request->setCookies();
        $cookies->write(new Cookie("test", "me"));
        $cookies->setFileToRead($this->file);
        $cookies->setFileToWrite($this->file);
        $response = $request->execute();
        $cookies->flushAll();
        $contents = file_get_contents($this->file);
        
        unlink($this->file);
        
        return new Result(strpos($contents, "test\tme") && strpos($response->getBody(), '"Cookie":"test=me"'));
    }
        
    public function flushAll()
    {
        return new Result(true, "tested via write");
    }
    
    
    public function reloadAll()
    {
    }
    
    
    public function deleteSession()
    {
        
    }
    
    
    public function deleteAll()
    {
        
    }
}
