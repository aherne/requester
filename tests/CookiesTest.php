<?php

namespace Test\Lucinda\URL;

use Lucinda\URL\Request;
use Lucinda\UnitTest\Result;
use Lucinda\URL\Cookies\Cookie;
use Lucinda\URL\Cookies;
use Lucinda\URL\Cookies\CookieFile;

class CookiesTest
{
    private $file;

    public function __construct()
    {
        $this->file = RECEIVER_FOLDER."/cookies.txt";
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

        $cookieFile = new CookieFile();
        return new Result($cookieFile->encrypt($cookies1[0]) != $cookieFile->encrypt($cookies2[0]));
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

        /* "test" is imported immediately via CURLOPT_COOKIELIST.  */
        $cookies->write(new Cookie("test", "me"));
        /* The list of cookies in cookies.txt will not be imported until right before a transfer is performed. Cookies in the list that have the same
        hostname, path and name as in "test" are skipped. That is because libcurl has already imported "test" and it's considered a "live"
        cookie. A live cookie will not be replaced by one read from a file. */
        $cookies->setFileToRead($this->file);
        /* Cookies are exported after curl_easy_cleanup is called. The server may have added, deleted or modified cookies by then. The cookies that
        were skipped on import are not exported. */
        $cookies->setFileToWrite($this->file);
        /* cookies imported from cookies.txt */
        $response = $request->execute();
        /* cookies exported to cookies.txt */
        $cookies->flushAll();
        $cookies->reloadAll();
        $contents = file_get_contents($this->file);

        unlink($this->file);
        // && strpos($response->getBody(), '"Cookie":"test=me"')
        return new Result(strpos($contents, "test\tme"));
    }

    public function flushAll()
    {
        return new Result(true, "tested via write");
    }

    public function reloadAll()
    {
        return new Result(true, "tested via write");
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

        $cookieFile = new CookieFile();
        return new Result(!empty($cookies1) && str_contains($cookieFile->encrypt($cookies1[0]), "PHPSESSID"));
    }
}
