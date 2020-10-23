<?php
namespace Lucinda\URL\Request;

use Lucinda\URL\FileNotFoundException;

/**
 * Encapsulates POST parameters to send in request
 */
class Parameters
{
    private $parameters = [];
    private $connection;
    
    /**
     * Sets cURL handle to perform operations on. Optionally includes key-value set of POST parameters to add already.
     * 
     * @param resource $curl
     * @param array $parameters
     */
    public function __construct($curl, array $parameters = [])
    {
        $this->connection = $curl;
        $this->parameters = $parameters;
    }
    
    /**
     * Adds a POST parameter by key and value (to be accessible as $_POST in response)
     * 
     * @param string $key
     * @param mixed $value
     */
    public function add(string $key, $value): void
    {
        $this->parameters[$key] = $value;
        \curl_setopt($this->connection, CURLOPT_POSTFIELDS, $this->parameters);
    }
    
    /**
     * Adds a POST parameter by key and file path/name (to be accessible as $_FILES in response)
     * 
     * @param string $key
     * @param string $path
     * @param string $name
     * @throws FileNotFoundException
     */
    public function addFile(string $key, string $path, string $name = ""): void
    {
        if (!file_exists($path)) {
            throw new FileNotFoundException($path);
        }
        $this->parameters[$key] = \curl_file_create($path, \mime_content_type($path), $name);
        \curl_setopt($this->connection, CURLOPT_POSTFIELDS, $this->parameters);
    }
}

