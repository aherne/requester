<?php
namespace Lucinda\URL\Response;

/**
 * Encapsulates basic response information based on curl handle received and calculated response time
 */
class Information
{
    private $url;
    private $info = [];
    private $cookies = [];
    private $duration;
    private $responseCode;
    
    /**
     * Sets information based on curl handle received as well as request duration calculated
     * 
     * @param resource $curl
     * @param float $duration
     */
    public function __construct($curl, float $duration)
    {
        $this->setCookies($curl);
        $this->setDuration($curl, $duration);
        $this->setStatusCode($curl);
        $this->setURL($curl);
        $this->setAll($curl);
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
            $this->duration = \curl_getinfo($curl, CURLINFO_TOTAL_TIME_T);
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
     * Sets all info collected by cURL driver
     * 
     * @param resource $curl
     */
    private function setAll($curl): void
    {
        $this->info = \curl_getinfo($curl);
    }
    
    /**
     * Gets all information received by curl handle (value of associative array @ https://www.php.net/manual/en/function.curl-getinfo.php) 
     * 
     * @return array
     */
    public function getAll(): array
    {
        return $this->info;
    }
}

