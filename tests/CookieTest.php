<?php
namespace Test\Lucinda\URL;
    
use Lucinda\URL\Cookie;
use Lucinda\UnitTest\Result;

class CookieTest
{
    private $object;
    
    public function __construct()
    {
        $this->object = new Cookie("name", "value");
    }
    
    public function setPath()
    {
        $this->object->setPath("/");
        return new Result(true, "tested via toString");        
    }
        

    public function setDomain()
    {
        $this->object->setDomain("example.com", false);
        return new Result(true, "tested via toString");
    }
        

    public function setMaxAge()
    {
        $this->object->setMaxAge(10);
        return new Result(true, "tested via toString");
    }
        

    public function setSecuredByHTTPS()
    {
        $this->object->setSecuredByHTTPS();
        return new Result(true, "tested via toString");
    }
        

    public function setSecuredByHTTPheaders()
    {
        $this->object->setSecuredByHTTPheaders();
        return new Result(true, "tested via toString");
    }
        

    public function toString()
    {
        return new Result($this->object->toString()=='example.com#HttpOnly_	FALSE	/	true	10	name	value');
    }
    
    
    public function toHeader()
    {
        return new Result($this->object->toHeader()=='name=value; Domain=example.com; Path=/; Max-Age=10; Secure; HttpOnly');
    }
        

}
