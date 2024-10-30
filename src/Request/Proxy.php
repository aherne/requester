<?php

namespace Lucinda\URL\Request;

use Lucinda\URL\Connection\Single as Connection;

/**
 * Encapsulates proxy ips and credentials
 */
class Proxy
{
    private $connection;

    /**
     * Sets connection to perform operations on.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Sets proxy connection information
     *
     * @param string $ip
     * @param int $port
     * @return void
     */
    public function setHost(string $ip, int $port): void
    {
        $this->connection->set(CURLOPT_PROXY, $ip.":".$port);
    }

    /**
     * Set proxy authentication credentials
     *
     * @param string $username
     * @param string $password
     * @return void
     */
    public function setAuthentication(string $username, string $password): void
    {
        $this->connection->set(CURLOPT_PROXYUSERPWD, $username.':'.$password);
    }
}