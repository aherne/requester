<?php
namespace Lucinda\URL\Request;

use Lucinda\URL\Cookies\Cookie;
use Lucinda\URL\Connection\Single as Connection;
use Lucinda\URL\Cookies\CookieHeader;

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
        "cookie"=>"addCookie",
    ];

    private $cookies = [];
    private $customHeaders = [];
    
    private $connection;
    
    /**
     * Sets connection to perform operations on.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
    
    /**
     * Compiles an If-Modified-Since header based on unix time received
     *
     * @param int $unixTime
     */
    public function setIfModifiedSince(int $unixTime): void
    {
        $this->connection->set(CURLOPT_TIMECONDITION, CURL_TIMECOND_IFMODSINCE);
        $this->connection->set(CURLOPT_TIMEVALUE, $unixTime);
    }
    
    /**
     * Compiles an If-Unmodified-Since header based on unix time received
     *
     * @param int $unixTime
     */
    public function setIfUnmodifiedSince(int $unixTime): void
    {
        $this->connection->set(CURLOPT_TIMECONDITION, CURL_TIMECOND_IFUNMODSINCE);
        $this->connection->set(CURLOPT_TIMEVALUE, $unixTime);
    }
    
    /**
     * Compiles an User-Agent header based on argument received
     *
     * @param string $userAgent
     */
    public function setUserAgent(string $userAgent): void
    {
        $this->connection->set(CURLOPT_USERAGENT, $userAgent);
    }
    
    /**
     * Compiles an Referer header based on argument received
     *
     * @param string $userAgent
     */
    public function setReferer(string $referer): void
    {
        $this->connection->set(CURLOPT_REFERER, $referer);
    }
    
    /**
     * Compiles a Cookie header based on argument received
     *
     * @param Cookie $cookie
     */
    public function addCookie(Cookie $cookie): void
    {
        $cookieHeader = new CookieHeader();
        $this->cookies[] = $cookieHeader->encrypt($cookie);

        $this->connection->set(CURLOPT_COOKIE, implode("; ", $this->cookies));
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
        $this->customHeaders[] = $name.": ".$value;
        $this->connection->set(CURLOPT_HTTPHEADER, $this->customHeaders);
    }
}
