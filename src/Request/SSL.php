<?php

namespace Lucinda\URL\Request;

use Lucinda\URL\FileNotFoundException;
use Lucinda\URL\Connection\Single as Connection;

/**
 * Encapsulates SSL options to use in request
 */
class SSL
{
    private Connection $connection;

    /**
     * Sets connection to perform operations on as well as file holding public PEM certificate.
     *
     * @param  Connection $connection
     * @param  string     $certificateAuthorityBundlePath
     * @throws FileNotFoundException.
     */
    public function __construct(Connection $connection, string $certificateAuthorityBundlePath)
    {
        $this->connection = $connection;

        if (!file_exists($certificateAuthorityBundlePath)) {
            throw new FileNotFoundException($certificateAuthorityBundlePath);
        }
        $this->connection->setOption(CURLOPT_CAINFO, $certificateAuthorityBundlePath);

        $this->connection->setOption(CURLOPT_SSL_VERIFYPEER, true);
        $this->connection->setOption(CURLOPT_SSL_VERIFYHOST, 2);
    }

    /**
     * Sets client SSL certificate by file path and optional password
     *
     * @param  string $path
     * @param  string $password
     * @throws FileNotFoundException
     */
    public function setCertificate(string $path, string $password=""): void
    {
        if (!file_exists($path)) {
            throw new FileNotFoundException($path);
        }
        $this->connection->setOption(CURLOPT_SSLCERT, $path);
        if ($password) {
            $this->connection->setOption(CURLOPT_SSLCERTPASSWD, $password);
        }
    }

    /**
     * Sets private keyfile for SSL certificate by file path and optional password
     *
     * @param  string $path
     * @param  string $password
     * @throws FileNotFoundException
     */
    public function setPrivateKey(string $path, string $password=""): void
    {
        if (!file_exists($path)) {
            throw new FileNotFoundException($path);
        }
        $this->connection->setOption(CURLOPT_SSLKEY, $path);
        if ($password) {
            $this->connection->setOption(CURLOPT_SSLKEYPASSWD, $password);
        }
    }
}
