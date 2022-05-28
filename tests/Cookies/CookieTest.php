<?php

namespace Test\Lucinda\URL\Cookies;

use Lucinda\URL\Cookies\Cookie;
use Lucinda\UnitTest\Result;

class CookieTest
{
    private $object;

    public function __construct()
    {
        $this->object = new Cookie("name", "value");
    }

    public function getName()
    {
        return new Result($this->object->getName()=="name");
    }

    public function getValue()
    {
        return new Result($this->object->getValue()=="value");
    }

    public function setPath()
    {
        $this->object->setPath("/abc");
        return new Result(true, "tested via getPath");
    }

    public function getPath()
    {
        return new Result($this->object->getPath()=="/abc");
    }


    public function setDomain()
    {
        $this->object->setDomain("example.com");
        return new Result(true, "tested via getDomain");
    }

    public function getDomain()
    {
        return new Result($this->object->getDomain()=="example.com");
    }

    public function setSubdomainsIncluded()
    {
        $this->object->setSubdomainsIncluded(true);
        return new Result(true, "tested via isSubdomainsIncluded");
    }

    public function isSubdomainsIncluded()
    {
        return new Result($this->object->isSubdomainsIncluded()==true);
    }


    public function setMaxAge()
    {
        $this->object->setMaxAge(10);
        return new Result(true, "tested via getMaxAge");
    }

    public function getMaxAge()
    {
        return new Result($this->object->getMaxAge()==10);
    }


    public function setSecuredByHttps()
    {
        $this->object->setSecuredByHTTPS();
        return new Result(true, "tested via isSecuredByHttps");
    }

    public function isSecuredByHttps()
    {
        return new Result($this->object->isSecuredByHttps()==true);
    }

    public function setSecuredByHttpHeaders()
    {
        $this->object->setSecuredByHTTPheaders();
        return new Result(true, "tested via isSecuredByHttpHeaders");
    }

    public function isSecuredByHttpHeaders()
    {
        return new Result($this->object->isSecuredByHttpHeaders()==true);
    }
}
