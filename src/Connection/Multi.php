<?php
namespace Lucinda\URL\Connection;

use Lucinda\URL\Response\Exception;

/**
 * Encapsulates a multi URL connection (enveloping curl_multi_* functions)
 */
class Multi
{
    private \CurlMultiHandle $connection;
    private array $children = [];
    
    /**
     * Initiates a new URL multi-connection
     */
    public function __construct()
    {
        $this->connection = \curl_multi_init();
    }
    
    /**
     * Automatically closes URL multi-connection created
     */
    public function __destruct()
    {
        foreach ($this->children as $child) {
            \curl_multi_remove_handle($this->connection, $child);
        }
        \curl_multi_close($this->connection);
    }
    
    /**
     * Adds a simple connection to pool
     *
     * @param Single $connection
     */
    public function add(Single $connection): void
    {
        $driver = $connection->getDriver();
        \curl_multi_add_handle($this->connection, $connection->getDriver());
        $this->children[(int) $driver] = $driver;
    }
    
    /**
     * Sets multi-connection option
     *
     * @param int $curlMultiOpt CURLMOPT_* constant
     * @param int|callable $value
     */
    public function set(int $curlMultiOpt, int|callable $value): void
    {
        \curl_multi_setopt($this->connection, $curlMultiOpt, $value);
    }
    
    /**
     * Executes multi-connection and returns response body for each connection pooled
     *
     * @param array $headers
     * @param bool $returnTransfer
     * @throws Exception
     * @return array
     */
    public function execute(array $headers, bool $returnTransfer = true): array
    {
        // executes multi handle
        $active = null;
        do {
            $status = curl_multi_exec($this->connection, $active);
            if ($status !== CURLM_OK) {
                throw new Exception(curl_multi_strerror($status), curl_multi_errno($this->connection));
            }
            if ($active) {
                curl_multi_select($this->connection);
            }
        } while ($active);
        
        // get responses
        $responses = [];
        while ($info = curl_multi_info_read($this->connection)) {
            if ($info["result"]!==CURLE_OK) {
                throw new Exception(curl_multi_strerror($info["result"]), curl_multi_errno($this->connection));
            }
            $key = (int) $info['handle'];
            $responses[$key] = $returnTransfer?curl_multi_getcontent($this->children[$key]):"";
        }
        
        return $responses;
    }
}
