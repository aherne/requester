<?php
namespace Lucinda\URL\Connection;

use Lucinda\URL\Response\Exception;

/**
 * Encapsulates a simple cURL connection (enveloping curl_* functions)
 */
class Single
{
    protected \CurlHandle $connection;
    
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
     * @param bool|int|string|callable|resource $value
     */
    public function set(int $option, mixed $value): void
    {
        \curl_setopt($this->connection, $option, $value);
    }
    
    /**
     * Gets connection option
     *
     * @param int $option CURLINFO_* constant
     * @return mixed
     */
    public function get(int $option): mixed
    {
        return \curl_getinfo($this->connection, $option);
    }
    
    /**
     * (ONLY FOR INTERNAL USAGE!) Builds a CURLFile object to be sent in POST requests based on arguments
     *
     * @param string $path
     * @param string $name
     * @return \CURLFile
     */
    public function createFile(string $path, string $name = ""): \CURLFile
    {
        return \curl_file_create($path, \mime_content_type($path), $name);
    }
    
    /**
     * Executes request and returns response body
     *
     * @throws Exception
     * @return string|bool
     */
    public function execute(): string|bool
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
     * @return \CurlHandle
     */
    public function getDriver(): \CurlHandle
    {
        return $this->connection;
    }
}
