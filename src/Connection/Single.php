<?php
namespace Lucinda\URL\Connection;

use Lucinda\URL\Response\Exception;

/**
 * Encapsulates a simple cURL connection (enveloping curl_* functions)
 */
class Single
{
    protected $connection;
    
    /**
     * Initiates a new URL connection
     */
    public function __construct()
    {
        $this->connection = \curl_init();
    }
    
    /**
     * Automatically closes URL connection created
     */
    public function __destruct()
    {
        \curl_close($this->connection);
    }
    
    /**
     * Sets connection option
     *
     * @param int $option CURLOPT_* constant
     * @param mixed $value
     */
    public function set(int $option, $value): void
    {
        \curl_setopt($this->connection, $option, $value);
    }
    
    /**
     * Gets connection option
     *
     * @param int $option CURLINFO_* constant
     * @return mixed
     */
    public function get(int $option)
    {
        return \curl_getinfo($this->connection, $option);
    }
    
    /**
     * (ONLY FOR INTERNAL USAGE!) Builds a CURLFile object to be sent in POST requests based on arguments
     *
     * @param string $path
     * @param string $name
     * @return mixed
     */
    public function createFile(string $path, string $name = "")
    {
        return \curl_file_create($path, \mime_content_type($path), $name);
    }
    
    /**
     * Executes request and returns response body
     *
     * @throws Exception
     * @return mixed
     */
    public function execute()
    {
        $body = curl_exec($this->connection);
        if ($body===false) {
            throw new Exception(curl_error($this->connection), curl_errno($this->connection));
        }
        return $body;
    }
    
    /**
     * (ONLY FOR INTERNAL USAGE!) Gets curl driver underneath. Necessary only for multiconnections!
     *
     * @return resource
     */
    public function getDriver()
    {
        return $this->connection;
    }
}
