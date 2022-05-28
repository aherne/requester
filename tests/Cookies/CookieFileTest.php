<?php

namespace Test\Lucinda\URL\Cookies;

use Lucinda\URL\Cookies\CookieFile;
use Lucinda\URL\Cookies\Cookie;
use Lucinda\UnitTest\Result;

class CookieFileTest
{
    public function encrypt()
    {
        $cookie = new Cookie("name", "value");
        $cookie->setDomain("example.com");
        $cookie->setPath("/");
        $cookie->setMaxAge(10);
        $cookie->setSecuredByHTTPheaders();
        $cookie->setSecuredByHTTPS();
        $cookie->setSubdomainsIncluded(true);

        $cookieFile = new CookieFile();
        return new Result($cookieFile->encrypt($cookie)=="example.com#HttpOnly_	TRUE	/	TRUE	10	name	value");
    }


    public function decrypt()
    {
        $cookieFile = new CookieFile();
        $cookie = $cookieFile->decrypt("example.com#HttpOnly_	TRUE	/	TRUE	10	name	value");
        return new Result(
            $cookie->getName()=="name" &&
            $cookie->getValue()=="value" &&
            $cookie->getDomain()=="example.com" &&
            $cookie->isSubdomainsIncluded()==true &&
            $cookie->getPath()=="/" &&
            $cookie->getMaxAge()==10 &&
            $cookie->isSecuredByHttpHeaders()==true &&
            $cookie->isSecuredByHttps()==true
        );
    }
}
