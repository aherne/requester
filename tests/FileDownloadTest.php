<?php

namespace Test\Lucinda\URL;

use Lucinda\UnitTest\Result;
use Lucinda\URL\FileDownload;
use Lucinda\URL\Request\Method;
use Lucinda\URL\Response\Progress;

class FileDownloadTest
{
    private $filePath;
    private $progressHandler;
    private $object;

    public function __construct()
    {
        $this->filePath = dirname(__DIR__).DIRECTORY_SEPARATOR."download.json";
        $this->progressHandler = new FileTransferProgress();
        $this->object = new FileDownload(RECEIVER_HTTP);
    }

    public function setMethod()
    {
        $this->object->setMethod(Method::GET);
        return new Result(true, "tested via execute");
    }


    public function setFile()
    {
        $this->object->setFile($this->filePath);
        return new Result(true, "tested via execute");
    }


    public function setCustomOption()
    {
        return new Result(true, "tested via RequestTest::setCustomOption");
    }


    public function setProgressHandler()
    {
        $this->object->setProgressHandler($this->progressHandler);
        return new Result(true, "tested via execute");
    }


    public function prepare()
    {
        $this->object->prepare();
        return new Result(true);
    }


    public function setURL()
    {
        return new Result(true, "tested via RequestTest::setURL");
    }


    public function setParameters()
    {
        return new Result(true, "operation not available for download");
    }


    public function setHeaders()
    {
        return new Result(true, "tested via RequestTest::setHeaders");
    }


    public function setSSL()
    {
        return new Result(true, "tested via RequestTest::setSSL");
    }


    public function getDriver()
    {
        return new Result(true, "tested via RequestTest::getDriver");
    }


    public function setReturnTransfer()
    {
        $this->object->setReturnTransfer(false);
        return new Result(true, "tested via execute");
    }


    public function execute()
    {
        $this->object->execute();
        $output = [];
        $output[] = new Result(sizeof($this->progressHandler->getChunks())>0, "tested progress handler");
        $json = json_decode(file_get_contents($this->filePath), true);
        unlink($this->filePath);
        $output[] = new Result($json["body"] == "OK", "tested download");
        return $output;
    }


    public function getConnection()
    {
        return new Result(true, "tested via Connection\MultiRequestTest or Connection\SharedRequestTest");
    }

    public function setRaw()
    {
        return new Result(true, "method not applicable");
    }
}
