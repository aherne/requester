<?php
namespace Lucinda\URL;

use Lucinda\URL\Request\Exception as RequestException;
use Lucinda\URL\Response\Exception as ResponseException;
use Lucinda\URL\Request\Headers;
use Lucinda\URL\Request\Cookies;
use Lucinda\URL\Request\SSL;
use Lucinda\URL\Request\Method;
use Lucinda\URL\Request\Parameters;

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
        CURLOPT_FOLLOWLOCATION=>"prepare",
        CURLOPT_HEADERFUNCTION=>"execute",
        CURLOPT_MAXREDIRS=>"prepare",
        CURLOPT_RETURNTRANSFER=>"prepare",
        CURLOPT_CONNECTTIMEOUT_MS=>"prepare"
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
     * (ONLY FOR INTERNAL USE) Gets cURL handle for SharedRequest and MultiRequest only. Only for internal usage!
     * 
     * @return resource
     */
    public function getDriver()
    {
        return $this->connection;
    }
        
    /**
     * (ONLY FOR INTERNAL USE) Validates request and prepares it for being sent. Only for internal usage!
     * 
     * @throws RequestException If request information is insufficient/invalid.
     */
    public function prepare(bool $returnTransfer = true, int $maxRedirectionsAllowed = 0, int $timeout = 300000): void
    {
        // validate url
        if (!$this->url) {
            throw new RequestException("Setting a URL is mandatory!");
        }
        
        // validate POST parameters
        if ($this->method == Method::POST && !$this->isPOST) {
            throw new RequestException("No parameters to POST!");
        }        
        if ($this->method != Method::POST && $this->isPOST) {
            throw new RequestException("Parameters can't be used unless request method is POST");
        }
        
        // validate SSL and sets certificate if missing
        if (strpos($this->url, "https")!==0 && $this->isSSL) {
            throw new RequestException("URL requested doesn't require SSL!");
        }
        if (strpos($this->url, "https")===0 && !$this->isSSL) {
            $this->setSSL(dirname(__DIR__).DIRECTORY_SEPARATOR."cacert.pem");
        }
        
        // sets redirection policy
        if($maxRedirectionsAllowed==0) {
            \curl_setopt($this->connection, CURLOPT_FOLLOWLOCATION, false);
        } else {
            \curl_setopt($this->connection, CURLOPT_FOLLOWLOCATION, true);
            \curl_setopt($this->connection, CURLOPT_MAXREDIRS, $maxRedirectionsAllowed);
        }
        
        // sets return transfer policy
        \curl_setopt($this->connection, CURLOPT_RETURNTRANSFER, $returnTransfer);
        
        // sets connection timeout
        \curl_setopt($this->connection, CURLOPT_CONNECTTIMEOUT_MS, $timeout);
    }
    
    /**
     * Validates request then executes it in order to produce a response
     * 
     * @param int $returnTransfer Whether or not response body should be returned
     * @param int $maxRedirectionsAllowed Maximum number of redirections allowed (if zero, it means none are)
     * @param int $timeout Connection timeout in milliseconds
     * @throws ResponseException If execution failed
     * @return Response
     */
    public function execute(bool $returnTransfer = true, int $maxRedirectionsAllowed = 0, int $timeout = 300000): Response
    {
        // validates request and prepares it for being sent
        $this->prepare($returnTransfer, $maxRedirectionsAllowed, $timeout);
        
        // registers response header processing
        $headers = [];
        if ($returnTransfer) {
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
        
        // executes request
        $startTime = microtime(true);
        $body = curl_exec($this->connection);
        $endTime = microtime(true);
        if ($body===false) {
            throw new ResponseException(curl_error($this->connection), curl_errno($this->connection));
        }
        
        // split headers from body
        return new Response($this->connection, $body, $headers, ($endTime-$startTime));
    }
}

