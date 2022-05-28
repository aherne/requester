<?php

namespace Test\Lucinda\URL\Cookies;

use Lucinda\URL\Cookies\Cookie;
use Lucinda\UnitTest\Result;
use Lucinda\URL\Cookies\CookieHeader;

class CookieHeaderTest
{
    public function encrypt()
    {
        $cookie = new Cookie("name", "value");
        $cookie->setDomain("example.com");
        $cookie->setSubdomainsIncluded(true);
        $cookie->setPath("/");
        $cookie->setMaxAge(10);
        $cookie->setSecuredByHTTPheaders();
        $cookie->setSecuredByHTTPS();

        $cookieFile = new CookieHeader();
        return new Result($cookieFile->encrypt($cookie)=="name=value; Domain=example.com; Path=/; Max-Age=10; Secure; HttpOnly");
    }


    public function decrypt()
    {
        $cookieFile = new CookieHeader();
        $cookie = $cookieFile->decrypt("name=value; Domain=example.com; Path=/; Max-Age=10; Secure; HttpOnly");
        return new Result(
            $cookie->getName()=="name" &&
            $cookie->getValue()=="value" &&
            $cookie->getDomain()=="example.com" &&
            $cookie->getPath()=="/" &&
            $cookie->getMaxAge()==10 &&
            $cookie->isSecuredByHttpHeaders()==true &&
            $cookie->isSecuredByHttps()==true
        );
    }
}
