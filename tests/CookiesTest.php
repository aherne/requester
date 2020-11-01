<?php
namespace Test\Lucinda\URL;

use Lucinda\URL\Request;
use Lucinda\UnitTest\Result;
use Lucinda\URL\Cookie;
use Lucinda\URL\Cookies;

class CookiesTest
{
    private $file;
    
    public function __construct()
    {
        $this->file = dirname(__DIR__).DIRECTORY_SEPARATOR."cookies.txt";
    }
    
    public function startNewSession()
    {
        file_put_contents($this->file, "");
        
        $request = new Request(RECEIVER_HTTP);
        $cookies = new Cookies($request->getConnection());
        $cookies->setFileToRead($this->file);
        $cookies->setFileToWrite($this->file);
        $request->execute();
        $cookies1 = $cookies->getAll();
        $cookies->flushAll();
        
        $request = new Request(RECEIVER_HTTP);
        $cookies = new Cookies($request->getConnection());
        $cookies->startNewSession(); // simulates browser restart
        $cookies->setFileToRead($this->file);
        $cookies->setFileToWrite($this->file);
        $request->execute();
        $cookies2 = $cookies->getAll();
        $cookies->flushAll();
        
        unlink($this->file);
        
        return new Result($cookies1[0]->toString() != $cookies2[0]->toString());
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
        $cookies = new Cookies($request->getConnection());
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
        file_put_contents($this->file, "");
        
        $request = new Request(RECEIVER_HTTP);
        $cookies = new Cookies($request->getConnection());
        $cookies->setFileToRead($this->file);
        $cookies->setFileToWrite($this->file);
        $request->execute();
        $cookies1 = $cookies->getAll();
        $cookies->deleteSession();
        $cookies2 = $cookies->getAll();
        
        unlink($this->file);
        
        return new Result(empty($cookies2) && !empty($cookies1));
    }
    
    
    public function deleteAll()
    {
        file_put_contents($this->file, "");
        
        $request = new Request(RECEIVER_HTTP);
        $cookies = new Cookies($request->getConnection());
        $cookies->write(new Cookie("test", "me"));
        $cookies->setFileToRead($this->file);
        $cookies->setFileToWrite($this->file);
        $request->execute();
        $cookies1 = $cookies->getAll();
        $cookies->deleteAll();
        $cookies2 = $cookies->getAll();
        
        unlink($this->file);
        
        return new Result(empty($cookies2) && !empty($cookies1));
    }
    public function getAll()
    {
        file_put_contents($this->file, "");
        
        $request = new Request(RECEIVER_HTTP);
        $cookies = new Cookies($request->getConnection());
        $cookies->setFileToRead($this->file);
        $cookies->setFileToWrite($this->file);
        $request->execute();
        $cookies1 = $cookies->getAll();
        
        unlink($this->file);
        
        return new Result(!empty($cookies1) && strpos($cookies1[0]->toString(), "PHPSESSID")!==false);
    }
        

}
