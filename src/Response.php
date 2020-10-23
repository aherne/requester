<?php
namespace Lucinda\URL;

use Lucinda\URL\Response\Information;

/**
 * Encapsulates all information about response received
 */
class Response
{
    private $info;
    private $body;
    private $headers;
    
    /**
     * Sets basic information, response body and headers
     * 
     * @param Information $info
     * @param string $body
     * @param array $headers
     */
    public function __construct(Information $info, string $body, array $headers)
    {
        $this->info = $info;
        $this->body = $body;
        $this->headers = $headers;
    }
    
    /**
     * Gets basic information (eg: HTTP status code) 
     * 
     * @return Information
     */
    public function getInfo(): Information
    {
        return $this->info;
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
     * Gets response headers by name and value
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}

