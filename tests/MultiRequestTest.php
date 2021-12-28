<?php
namespace Test\Lucinda\URL;

use Lucinda\URL\MultiRequest;
use Lucinda\URL\Request;
use Lucinda\UnitTest\Result;

class MultiRequestTest
{
    const URLS_TESTED = [
        "https://www.lucinda-framework.com/tutorials/application",
        "https://www.lucinda-framework.com/tutorials/mvc",
        "https://www.lucinda-framework.com/tutorials/event-listeners"
    ];
    private $object;
    
    public function __construct()
    {
        $this->object = new MultiRequest();
    }

    public function add()
    {
        foreach (self::URLS_TESTED as $url) {
            $this->object->add(new Request($url));
        }
        return new Result(true);
    }
        

    public function setCustomOption()
    {
        $this->object->setCustomOption(CURLMOPT_MAXCONNECTS, 100);
        return new Result(true);
    }
        

    public function execute()
    {
        $output = [];
        $results = $this->object->execute();
        
        $i=0;
        foreach ($results as $response) {
            $output[] = new Result($response->getStatusCode()==200 && $response->getURL()==self::URLS_TESTED[$i]);
            $i++;
        }
        return $output;
    }
}
