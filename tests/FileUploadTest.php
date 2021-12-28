<?php
namespace Test\Lucinda\URL;

use Lucinda\URL\FileUpload;
use Lucinda\URL\Request\Method;
use Lucinda\UnitTest\Result;

class FileUploadTest
{
    private $sourceFilePath;
    private $destinationFilePath;
    private $progressHandler;
    private $object;
    
    public function __construct()
    {
        $this->sourceFilePath = dirname(__DIR__).DIRECTORY_SEPARATOR."composer.json";
        $this->destinationFilePath = RECEIVER_FOLDER."/upload.json";
        $this->progressHandler = new FileTransferProgress();
        $this->object = new FileUpload(RECEIVER_HTTP);
    }

    public function setMethod()
    {
        $this->object->setMethod(Method::PUT);
        return new Result(true, "tested via execute");
    }
        

    public function setFile()
    {
        $this->object->setFile($this->sourceFilePath);
        return new Result(true, "tested via execute");
    }
        

    public function setParameters()
    {
        return new Result(true, "operation not available for upload");
    }
        

    public function setRaw()
    {
        $object = new FileUpload(RECEIVER_HTTP);
        $object->setMethod(Method::POST);
        $object->setRaw(file_get_contents($this->sourceFilePath));
        $object->execute();
        $json = json_decode(file_get_contents($this->destinationFilePath), true);
        unlink($this->destinationFilePath);
        return new Result($json["name"] == "lucinda/requester", "tested upload");
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

    public function execute()
    {
        $this->object->execute();
        $output = [];
        $output[] = new Result(sizeof($this->progressHandler->getChunks())>0, "tested progress handler");
        $json = json_decode(file_get_contents($this->destinationFilePath), true);
        unlink($this->destinationFilePath);
        $output[] = new Result($json["name"] == "lucinda/requester", "tested upload");
        return $output;
    }
        

    public function getConnection()
    {
        return new Result(true, "tested via Connection\MultiRequestTest or Connection\SharedRequestTest");
    }
}
