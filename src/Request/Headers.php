<?php
namespace Lucinda\URL\Request;

/**
 * Encapsulates HTTP request headers to send
 */
class Headers
{
    private const COVERED_HEADERS = [
        "if-modified-since"=>"setIfModifiedSince",
        "if-unmodified-since"=>"setIfUnmodifiedSince",
        "user-agent"=>"setUserAgent",
        "referer"=>"setReferer",
        "cookie"=>"setCookie",
    ];
    
    private $ifModifiedSince;
    private $ifUnmodifiedSince;
    private $userAgent;
    private $referer;
    private $oauth2Bearer;
    private $customHeaders = [];
    
    private $connection;
    
    /**
     * Sets cURL handle to perform operations on.
     * 
     * @param resource $curl
     */
    public function __construct($curl)
    {
        $this->connection = $curl;
    }
    
    /**
     * Compiles an If-Modified-Since header based on unix time received
     * 
     * @param int $unixTime
     */
    public function setIfModifiedSince(int $unixTime): void
    {
        curl_setopt($this->connection, CURLOPT_HEADER, true);
        curl_setopt($this->connection, CURLOPT_TIMECONDITION, CURL_TIMECOND_IFMODSINCE);
        curl_setopt($this->connection, CURLOPT_TIMEVALUE, $unixTime);
    }
    
    /**
     * Compiles an If-Unmodified-Since header based on unix time received
     *
     * @param int $unixTime
     */
    public function setIfUnmodifiedSince(int $unixTime): void
    {
        curl_setopt($this->connection, CURLOPT_HEADER, true);
        curl_setopt($this->connection, CURLOPT_TIMECONDITION, CURL_TIMECOND_IFUNMODSINCE);
        curl_setopt($this->connection, CURLOPT_TIMEVALUE, $unixTime);
    }
    
    /**
     * Compiles an User-Agent header based on argument received
     * 
     * @param string $userAgent
     */
    public function setUserAgent(string $userAgent): void
    {
        curl_setopt($this->connection, CURLOPT_USERAGENT, $userAgent);
    }
    
    /**
     * Compiles an Referer header based on argument received
     *
     * @param string $userAgent
     */
    public function setReferer(string $referer): void
    {
        curl_setopt($this->connection, CURLOPT_REFERER, $referer);
    }
    
    /**
     * Compiles an Authorization Bearer header based on OAuth2 access token received
     * 
     * @param string $accessToken
     */
    public function setOAuth2Bearer(string $accessToken): void
    {
        curl_setopt($this->connection, CURLOPT_XOAUTH2_BEARER, $accessToken);
    }
    
    /**
     * Compiles a Cookie header based on argument received
     * 
     * @param string $cookie
     */
    public function setCookie(string $cookie): void
    {
        curl_setopt($this->connection, CURLOPT_COOKIE, $cookie);
    }
    
    /**
     * Adds a custom HTTP request header not covered already
     * 
     * @param string $name
     * @param string $value
     * @throws Exception If header is already covered by one of specialized class methodss
     */
    public function addCustomHeader(string $name, string $value): void
    {
        $lowerName = trim(strtolower($name));
        if (isset(self::COVERED_HEADERS[$lowerName])) {
            throw new Exception("Header already covered by ".self::COVERED_HEADERS[$lowerName]." method!");
        }
        if ($lowerName=="Authorization" && strpos(trim(strtolower($value)), "bearer ")===0) {
            throw new Exception("Header already covered by setOAuth2Bearer method!");
        }
        $this->customHeaders[] = $name.": ".$value;
        curl_setopt($this->connection, CURLOPT_HTTPHEADER, $this->customHeaders);
    }
}

