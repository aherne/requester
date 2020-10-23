<?php
namespace Lucinda\URL\Response;

/**
 * Encapsulates basic response information based on curl handle received and calculated response time
 */
class Information
{
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
        // TODO: see the format
        $this->cookies = \curl_getinfo($curl, CURLINFO_COOKIELIST);
        $this->duration = $duration;
        $this->responseCode = \curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $this->info = \curl_getinfo($curl);
    }
    
    /**
     * Gets total duration in milliseconds by whom response was received
     * 
     * @return float
     */
    public function getDuration(): float
    {
        return $this->duration;
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
     * Gets response HTTP status code
     * 
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->responseCode;
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

