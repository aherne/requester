<?php
namespace Lucinda\URL\Connection;

/**
 * Encapsulates a shared URL connection (enveloping curl_share_* functions)
 */
class Shared
{
    private \CurlShareHandle $connection;
    
    /**
     * Initiates a shared URL connection
     */
    public function __construct()
    {
        $this->connection = \curl_share_init();
    }
    
    /**
     * Automatically closes shared multi-connection created
     */
    public function __destruct()
    {
        \curl_share_close($this->connection);
    }
    
    /**
     * Adds a simple connection to pool
     *
     * @param Single $connection
     */
    public function add(Single $connection): void
    {
        \curl_setopt($connection->getDriver(), CURLOPT_SHARE, $this->connection);
    }
    
    /**
     * Sets cookie share option
     *
     * @param int $option CURLSHOPT_* constant
     * @param int $value
     */
    public function set(int $option, int $value): void
    {
        \curl_share_setopt($this->connection, $option, $value);
    }
}
