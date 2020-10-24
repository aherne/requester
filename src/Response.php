<?php
namespace Lucinda\URL;

/**
 * Encapsulates all information about response received
 */
class Response
{
    private $url;
    private $info = [];
    private $cookies = [];
    private $duration;
    private $responseCode;
    private $body;
    private $headers;
    
    /**
     * Sets basic information, response body and headers
     * 
     * @param resource $curl
     * @param string $body
     * @param array $headers
     * @param float $duration
     */
    public function __construct($curl, string $body, array $headers, float $duration = 0)
    {
        $this->setDriverInfo($curl);
        $this->setCookies($curl);
        $this->setDuration($curl, $duration);
        $this->setStatusCode($curl);
        $this->setURL($curl);
        $this->setBody($body);
        $this->setHeaders($headers);
    }
    
    /**
     * Sets all information received from cURL handle
     *
     * @param resource $curl
     */
    private function setDriverInfo($curl): void
    {
        $this->info = \curl_getinfo($curl);
    }
    
    /**
     * Gets all information received from cURL handle (associative array @ https://www.php.net/manual/en/function.curl-getinfo.php)
     *
     * @return array
     */
    public function getDriverInfo(): array
    {
        return $this->info;
    }
    
    /**
     * Sets list of cookies received from response
     *
     * @param resource $curl
     */
    private function setCookies($curl): void
    {
        $this->cookies = \curl_getinfo($curl, CURLINFO_COOKIELIST);
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
     * @param resource $curl
     * @param float $duration Total duration calculated by PHP
     */
    private function setDuration($curl, float $duration): void
    {
        if ($duration===0) {
            $this->duration = round($duration*1000);
        } else if (defined("CURLINFO_TOTAL_TIME_T")) {
            $this->duration = round(\curl_getinfo($curl, CURLINFO_TOTAL_TIME_T)/1000);
        } else {
            $this->duration = \curl_getinfo($curl, CURLINFO_TOTAL_TIME)*1000;
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
     *
     * @param resource $curl
     */
    private function setStatusCode($curl): void
    {
        $this->responseCode = \curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
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
     *
     * @param resource $curl
     */
    private function setURL($curl): void
    {
        $this->url = \curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);
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
    private function setBody(string $body): string
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
    public function setHeaders(array $headers): array
    {
        return $this->headers;
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

