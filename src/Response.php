<?php
namespace Lucinda\URL;

use Lucinda\URL\Response\Exception as ResponseException;
use Lucinda\URL\Connection\Single as Connection;

/**
 * Encapsulates all information about response received
 */
class Response
{
    private const COVERED_OPTIONS = [
        CURLINFO_COOKIELIST=>"setCookies",
        CURLINFO_TOTAL_TIME=>"setDuration",
        CURLINFO_RESPONSE_CODE=>"setStatusCode",
        CURLINFO_EFFECTIVE_URL=>"setURL"
    ];
    
    private $connection;
    
    private $url;
    private $cookies = [];
    private $duration;
    private $responseCode;
    private $body;
    private $headers;
    
    /**
     * Sets basic information, response body and headers
     * 
     * @param Connection $connection
     * @param string $body
     * @param array $headers
     * @param float $duration
     */
    public function __construct(Connection $connection, string $body, array $headers, float $duration = 0)
    {
        $this->connection = $connection;
        
        $this->setCookies();
        $this->setDuration($duration);
        $this->setStatusCode();
        $this->setURL();
        $this->setBody($body);
        $this->setHeaders($headers);
    }
    
    /**
     * Gets obscure CURLINFO not already covered by API.
     *
     * @param int $curlinfo Curlinfo option (eg: CURLINFO_PRIVATE)
     * @throws ResponseException If option already covered
     */
    public function getCustomOption(int $curlinfo)
    {
        if (isset(self::COVERED_OPTIONS[$curlinfo])) {
            throw new ResponseException("Option already covered by ".self::COVERED_OPTIONS[$curlinfo]." method!");
        }
        return $this->connection->get($curlinfo);
    }
    
    /**
     * Sets list of cookies received from response
     */
    private function setCookies(): void
    {
        $temp = $this->connection->get(CURLINFO_COOKIELIST);
        $cookies = [];
        foreach($temp as $cookie) {
            $parts = explode("\t", $cookie);
            $cookie = new Cookie($parts[5], $parts[6]);
            if (stripos($parts[0], "#HttpOnly_")) {
                $cookie->setDomain(str_replace("#HttpOnly_", "", $parts[0]), ($parts[1]=="TRUE"));
                $cookie->setSecuredByHTTPheaders();
            } else {
                $cookie->setDomain($parts[0], ($parts[1]=="TRUE"));
            }
            $cookie->setPath($parts[2]);
            if ($parts[3] == "TRUE") {
                $cookie->setSecuredByHTTPheaders();
            }
            $cookie->setMaxAge((int) $parts[4]);
            $cookies[] = $cookie;
        }
        $this->cookies = $cookies;
    }
    
    /**
     * Gets list of cookies received from response
     *
     * @return array
     */
    public function getCookies(): array
    {
        return $this->cookies;
    }
    
    /**
     * Sets total response duration in milliseconds
     *
     * @param float $duration Total duration calculated by PHP
     */
    private function setDuration(float $duration): void
    {
        if ($duration===0) {
            $this->duration = round($duration*1000);
        } else if (defined("CURLINFO_TOTAL_TIME_T")) {
            $this->duration = round($this->connection->get(CURLINFO_TOTAL_TIME_T)/1000);
        } else {
            $this->duration = $this->connection->get(CURLINFO_TOTAL_TIME)*1000;
        }
    }
    
    /**
     * Gets total duration in milliseconds by whom response was received
     *
     * @return int
     */
    public function getDuration(): int
    {
        return $this->duration;
    }
    
    /**
     * Sets response HTTP status code
     */
    private function setStatusCode(): void
    {
        $this->responseCode = $this->connection->get(CURLINFO_RESPONSE_CODE);
    }
    
    /**
     * Gets response HTTP status code
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->responseCode;
    }
    
    /**
     * Sets URL requested
     */
    private function setURL(): void
    {
        $this->url = $this->connection->get(CURLINFO_EFFECTIVE_URL);
    }
    
    /**
     * Gets url requested
     *
     * @return string
     */
    public function getURL(): string
    {
        return $this->url;
    }
    
    /**
     * Sets response body
     *
     * @param string $body
     */
    private function setBody(string $body): void
    {
        $this->body = $body;
    }
    
    /**
     * Gets response body
     * 
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }
    
    /**
     * Sets response headers by name and value
     *
     * @param array $headers
     */
    private function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }
    
    /**
     * Gets response headers by name and value
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}

