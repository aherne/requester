<?php
namespace Lucinda\URL\Request;

use Lucinda\URL\FileNotFoundException;

/**
 * Encapsulates SSL options to use in request
 */
class SSL
{
    private $connection;
    
    /**
     * Sets cURL handle to perform operations on as well as file holding public PEM certificate.
     * 
     * @param resource $curl
     * @param string $certificateAuthorityBundlePath
     * @throws FileNotFoundException.
     */
    public function __construct($curl, string $certificateAuthorityBundlePath)
    {
        $this->connection = $curl;
        
        if (!file_exists($certificateAuthorityBundlePath)) {
            throw new FileNotFoundException($certificateAuthorityBundlePath);
        }
        \curl_setopt($this->connection, CURLOPT_CAPATH, $certificateAuthorityBundlePath);
        
        
        \curl_setopt($this->connection, CURLOPT_SSL_VERIFYPEER, true);
        \curl_setopt($this->connection, CURLOPT_SSL_VERIFYHOST, 2);
    }
    
    /**
     * Sets client SSL certificate by file path and optional password
     * 
     * @param string $path
     * @param string $password
     * @throws FileNotFoundException
     */
    public function setCertificate(string $path, string $password=""): void
    {
        if (!file_exists($path)) {
            throw new FileNotFoundException($path);
        }
        \curl_setopt($this->connection, CURLOPT_SSLCERT, $path);
        if ($password) {
            \curl_setopt($this->connection, CURLOPT_SSLCERTPASSWD, $password);
        }
    }
    
    /**
     * Sets private keyfile for SSL certificate by file path and optional password
     * 
     * @param string $path
     * @param string $password
     * @throws FileNotFoundException
     */
    public function setPrivateKey(string $path, string $password=""): void
    {
        if (!file_exists($path)) {
            throw new FileNotFoundException($path);
        }
        \curl_setopt($this->connection, CURLOPT_SSLKEY, $path);
        if ($password) {
            \curl_setopt($this->connection, CURLOPT_SSLKEYPASSWD, $password);
        }
    }    
}

