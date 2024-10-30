<?php
namespace Test\Lucinda\URL;

use Lucinda\URL\Request;
use Lucinda\UnitTest\Result;
use Lucinda\URL\Request\Method;
use Lucinda\URL\Cookies\Cookie;
use Lucinda\URL\Connection\Single as Connection;

class RequestTest
{
    public function setURL()
    {
        $request = new Request();
        $request->setURL(RECEIVER_HTTP);
        $response = $request->execute();
        $payload = json_decode($response->getBody(), true);
        return new Result($response->getStatusCode() == 200 && $payload["body"]=="OK");
    }
        

    public function setMethod()
    {
        $request = new Request(RECEIVER_HTTP);
        $request->setMethod(Method::POST);
        $request->setParameters(["a"=>"b", "c"=>"d"]);
        $response = $request->execute();
        $payload = json_decode($response->getBody(), true);
        return new Result($payload["server"]["REQUEST_METHOD"]=="POST");
    }
        

    public function setParameters()
    {
        $parameters = ["a"=>"b", "c"=>"d"];
        $request = new Request(RECEIVER_HTTP);
        $request->setMethod(Method::POST);
        $request->setParameters($parameters);
        $response = $request->execute();
        $payload = json_decode($response->getBody(), true);
        return new Result($payload["request"] == $parameters);
    }
        

    public function setHeaders()
    {
        $request = new Request(RECEIVER_HTTP);
        $headers = $request->setHeaders();
        $headers->addCookie(new Cookie("key", "value"));
        $headers->setIfModifiedSince(time()+10);
        $headers->setReferer("http://www.example.com");
        $headers->setUserAgent("Google Chrome");
        $headers->addCustomHeader("Content-Type", "application/json");
        $response = $request->execute();
        $payload = json_decode($response->getBody(), true);
        $receivedHeaders = $payload["headers"];
        return new Result(
            $receivedHeaders["Cookie"] == "key=value; Path=/" &&
            strtotime($receivedHeaders["If-Modified-Since"]) == time()+10 &&
            $receivedHeaders["Referer"]=="http://www.example.com" &&
            $receivedHeaders["User-Agent"]=="Google Chrome" &&
            $receivedHeaders["Content-Type"]=="application/json"
        );
    }

    public function setProxy()
    {
        return new Result(true, "proxies cannot be unit tested");
    }
        

    public function setSSL()
    {
        $request = new Request(RECEIVER_HTTPS);
        $request->setSSL(dirname(__DIR__).DIRECTORY_SEPARATOR."certificates".DIRECTORY_SEPARATOR."cacert.pem");
        $response = $request->execute();
        $payload = json_decode($response->getBody(), true);
        return new Result($response->getStatusCode() == 200 && $payload["body"]=="OK");
    }
        

    public function setCustomOption()
    {
        $request = new Request(RECEIVER_HTTP);
        $request->setCustomOption(CURLOPT_PRIVATE, "private data");
        $response = $request->execute();
        return new Result($response->getCustomOption(CURLINFO_PRIVATE) == "private data");
    }

    public function getConnection()
    {
        $request = new Request(RECEIVER_HTTP);
        return new Result($request->getConnection() instanceof Connection);
    }
        

    public function prepare()
    {
        $request = new Request(RECEIVER_HTTP);
        $request->prepare();
        return new Result(true);
    }
        

    public function execute()
    {
        $output = [];
        
        // perform a HTTP request
        $request = new Request(RECEIVER_HTTP);
        $response = $request->execute();
        $payload = json_decode($response->getBody(), true);
        $output[] = new Result($response->getStatusCode() == 200 && $payload["body"]=="OK", "tested HTTP");
        
        // perform a HTTP request
        $request = new Request(RECEIVER_HTTP);
        ob_start();
        $response = $request->execute(false);
        ob_end_clean();
        $output[] = new Result($response->getStatusCode() == 200 && $response->getBody()=="1", "tested HTTP no body");
        
        // perform a HTTPs request
        $request = new Request(RECEIVER_HTTPS);
        $response = $request->execute();
        $payload = json_decode($response->getBody(), true);
        $output[] = new Result($response->getStatusCode() == 200 && $payload["body"]=="OK", "tested HTTPS");
        
        return $output;
    }
    public function setRaw()
    {
        $parameters = ["a"=>"b", "c"=>"d"];
        $request = new Request(RECEIVER_HTTP);
        $request->setMethod(Method::PUT);
        $request->setRaw(http_build_query($parameters));
        $response = $request->execute();
        $payload = json_decode($response->getBody(), true);
        return new Result($payload["request"] == http_build_query($parameters));
    }
        

}
