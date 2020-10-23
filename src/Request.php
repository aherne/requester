<?php
namespace Lucinda\URL;

use Lucinda\URL\Request\Exception as RequestException;
use Lucinda\URL\Response\Exception as ResponseException;
use Lucinda\URL\Request\Headers;
use Lucinda\URL\Request\Cookies;
use Lucinda\URL\Request\SSL;
use Lucinda\URL\Request\Method;
use Lucinda\URL\Request\Parameters;
use Lucinda\URL\Response\Information;

/**
 * Encapsulates a GET HTTP/HTTPS request for any resource
 */
class Request
{
    private const COVERED_OPTIONS = [
        CURLOPT_URL=>"setURL",
        CURLOPT_POST=>"setMethod",
        CURLOPT_NOBODY=>"setMethod",
        CURLOPT_CUSTOMREQUEST=>"setMethod",
        CURLOPT_COOKIESESSION=>"setCookies",
        CURLOPT_COOKIEFILE=>"setCookies",
        CURLOPT_COOKIEJAR=>"setCookies",
        CURLOPT_COOKIELIST=>"setCookies",
        CURLOPT_HEADER=>"setHeaders",
        CURLOPT_TIMECONDITION=>"setHeaders",
        CURLOPT_TIMEVALUE=>"setHeaders",
        CURLOPT_USERAGENT=>"setHeaders",
        CURLOPT_REFERER=>"setHeaders",
        CURLOPT_XOAUTH2_BEARER=>"setHeaders",
        CURLOPT_COOKIE=>"setHeaders",
        CURLOPT_HTTPHEADER=>"setHeaders",
        CURLOPT_POSTFIELDS=>"setParameters",
        CURLOPT_CAPATH=>"setSSL",
        CURLOPT_SSL_VERIFYPEER=>"setSSL",
        CURLOPT_SSL_VERIFYHOST=>"setSSL",
        CURLOPT_SSLCERT=>"setSSL",
        CURLOPT_SSLCERTPASSWD=>"setSSL",
        CURLOPT_SSLKEY=>"setSSL",
        CURLOPT_SSLKEYPASSWD=>"setSSL",
        CURLOPT_FOLLOWLOCATION=>"send",
        CURLOPT_HEADERFUNCTION=>"send",
        CURLOPT_MAXREDIRS=>"send",
        CURLOPT_RETURNTRANSFER=>"send",
        CURLOPT_CONNECTTIMEOUT_MS=>"send"
    ];
    
    protected $url;
    protected $method;
    
    protected $isSSL = false;
    protected $isPOST = false;
    protected $connection;
    
    /**
     * Initiates a new URL connection or imports existing cURL handler
     * 
     * @param resource $curl
     */
    public function __construct($curl = null)
    {
        if ($curl) {
            $this->connection = $curl;
        } else {
            $this->connection = \curl_init();
        }
    }
    
    /**
     * Automatically closes URL connection created
     */
    public function __destruct()
    {
        \curl_close($this->connection);
    }
    
    /**
     * Sets URL of requested resource
     * 
     * @param string $url
     * @throws RequestException If URL is invalid
     */
    public function setURL(string $url): void
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new RequestException("URL is invalid: ".$url);
        }
        \curl_setopt($this->connection, CURLOPT_URL, $url);
        $this->url = $url;
    }
    
    /**
     * Sets HTTP method to use in requesting resource. If not set, GET is used by default!
     * 
     * @param Method $method One of enum values (eg: Method::POST)
     * @throws RequestException If HTTP method is invalid
     */
    public function setMethod(string $method): void
    {
        switch($method)
        {
            case Method::GET:
                // do nothing
                break;
            case Method::POST:
                \curl_setopt($this->connection, CURLOPT_POST, true);
                break;
            case Method::HEAD:
                \curl_setopt($this->connection, CURLOPT_NOBODY, true);
                break;
            case Method::PUT:
            case Method::DELETE:
            case Method::OPTIONS:
            case Method::CONNECT:
            case Method::TRACE:
            case Method::PATCH:
                \curl_setopt($this->connection, CURLOPT_CUSTOMREQUEST, $method);
                break;
            default:
                throw new RequestException("Invalid request method");
                break;
        }
        $this->method = $method;
    }
    
    /**
     * Sets parameters to send in POST requests through Parameters object returned.
     * 
     * @param array $parameters Optional key-value set of POST parameters to send already.
     * @return Parameters
     */
    public function setParameters(array $parameters = []): Parameters
    {
        $this->isPOST = true;
        return new Parameters($this->connection, $parameters);
    }
    
    /**
     * Sets HTTP headers to send through Headers object returned. 
     * 
     * @return Headers
     */
    public function setHeaders(): Headers
    {
        return new Headers($this->connection);
    }
    
    /**
     * Sets session/cookies policy through Cookies object returned.
     *
     * @return Headers
     */
    public function setCookies(): Cookies
    {
        return new Cookies($this->connection);
    }
    
    /**
     * Sets SQL policy through SSL object returned.
     * 
     * @param string $certificateAuthorityBundlePath
     * @return SSL
     */
    public function setSSL(string $certificateAuthorityBundlePath): SSL
    {
        $this->isSSL = true;
        return new SSL($this->connection, $certificateAuthorityBundlePath);
    }
    
    /**
     * Sets obscure cURL option not already covered by API.
     * 
     * @param int $curlopt Curlopt option key (eg: CURLOPT_PRIVATE)
     * @param mixed $value
     * @throws RequestException If HTTP method is invalid
     */
    public function setCustomOption(int $curlopt, $value): void
    {
        if (isset(self::COVERED_OPTIONS[$curlopt])) {
            throw new RequestException("Option already covered by ".self::COVERED_OPTIONS[$curlopt]." method!");
        }
        \curl_setopt($this->connection, $curlopt, $value);
    }
    
    /**
     * Clones connection handle to be used in another request
     * 
     * @return Request
     */
    public function clone(): Request
    {
        return new Request(\curl_copy_handle($this->connection));
    }
    
    /**
     * Gets cURL handle for SharedRequest and MultiRequest only. Developers MUST NOT use this method!
     * 
     * @return resource
     */
    public function getDriver()
    {
        return $this->connection;
    }
        
    /**
     * Validates if request information is (correct) enough to produce a response
     * 
     * @throws RequestException If request information is insufficient/invalid.
     */
    protected function validate(): void
    {
        // validate url
        if (!$this->url) {
            throw new RequestException("Setting a URL is mandatory!");
        }
        
        // validate POST parameters
        if ($this->method == Method::POST && !$this->isPOST) {
            throw new RequestException("No parameters or raw body to POST!");
        }        
        if ($this->method != Method::POST && $this->isPOST) {
            throw new RequestException("Parameters can't be used unless method is POST");
        }
        
        // validate SSL
        if (strpos($this->url, "https")!==0 && $this->isSSL) {
            throw new RequestException("URL requested doesn't require SSL!");
        }
    }
    
    /**
     * Sets handler that will be used in parsing response headers.
     * 
     * @param array $headers
     */
    protected function setHeadersHandler(array &$headers): void
    {
        \curl_setopt($this->connection, CURLOPT_HEADERFUNCTION,
            function($curl, $header) use (&$headers)
            {
                $len = strlen($header);
                $header = explode(':', $header, 2);
                // ignore invalid headers
                if (count($header) < 2) {
                    return $len;
                } else {
                    $headers[strtolower(trim($header[0]))][] = trim($header[1]);
                    return $len;
                }
            }
        );
    }
    
    /**
     * Sends request to produce a response. If resource is requested via HTTPS and no certificate was set, uses 'cacert.pem' from parent folder 
     * (source: https://curl.haxx.se/ca/cacert.pem)
     * 
     * @param int $maxRedirectionsAllowed Number of max redirections allowed. Special values: -1 (infinite), 0 (none)
     * @param int $timeout Connection timeout in milliseconds
     * @return Response
     */
    public function send(int $maxRedirectionsAllowed = -1, int $timeout = 300000): Response
    {
        // validates selection
        $this->validate();
        
        // use default certificate if none given
        if (strpos($this->url, "https")===0 && !$this->isSSL) {
            $this->setSSL(dirname(__DIR__).DIRECTORY_SEPARATOR."cacert.pem");
        }
        
        // sets redirection rules
        \curl_setopt($this->connection, CURLOPT_FOLLOWLOCATION, $maxRedirectionsAllowed!=0);
        \curl_setopt($this->connection, CURLOPT_MAXREDIRS, $maxRedirectionsAllowed);
        
        // sets transfer to be always returned
        \curl_setopt($this->connection, CURLOPT_RETURNTRANSFER, true);
        
        // sets connection timeout
        \curl_setopt($this->connection, CURLOPT_CONNECTTIMEOUT_MS, $timeout);
                
        return $this->execute();
    }
    
    /**
     * Executes request to produce a response
     * 
     * @throws ResponseException If execution failed
     * @return Response
     */
    protected function execute(): Response
    {        
        // registers response header processing
        $headers = [];
        $this->setHeadersHandler($headers);
        
        // executes request
        $startTime = microtime(true);
        $body = curl_exec($this->connection);
        $endTime = microtime(true);
        if ($body===false) {
            throw new ResponseException(curl_error($this->connection), curl_errno($this->connection));
        }
        
        // split headers from body
        return new Response(new Information($this->connection, ($endTime-$startTime)), $body, $headers);
    }
}

