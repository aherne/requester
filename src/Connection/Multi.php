<?php
namespace Lucinda\URL\Connection;

use Lucinda\URL\Response\Exception;

/**
 * Encapsulates a multi URL connection (enveloping curl_multi_* functions)
 */
class Multi
{
    private $connection;
    private $children = [];
    
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
     * @param int $option CURLMOPT_* constant
     * @param mixed $value
     */
    public function set(int $curlMultiOpt, $value): void
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
    public function execute(&$headers, bool $returnTransfer = true): array
    {
        // executes multi handle
        $active = null;
        do {
            $status = curl_multi_exec($this->connection, $active);
            if ($status !== CURLM_OK) {
                echo __LINE__."#".$status."\n";
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
                echo __LINE__."#".$info["result"]."\n";
                throw new Exception(curl_multi_strerror($info["result"]), curl_multi_errno($this->connection));
            }
            $key = (int) $info['handle'];
            $responses[$key] = $returnTransfer?curl_multi_getcontent($this->children[$key]):"";
        }
        
        return $responses;
    }
}
