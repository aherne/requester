<?php
namespace Lucinda\URL\Request;

use Lucinda\URL\FileNotFoundException;
use Lucinda\URL\Cookie\Cookie;

/**
 * Encapsulates operations in working with request/response cookies
 */
class Cookies
{
    private $connection;
    
    /**
     * Sets cURL handle to perform operations on.
     * 
     * @param resource $curl
     */
    public function __construct($curl)
    {
        $this->connection = $curl;
    }
    
    /**
     * Starts a new session cookie, ignoring that existing
     */
    public function startNewSession(): void
    {
        \curl_setopt($this->connection, CURLOPT_COOKIESESSION, true);
    }
    
    /**
     * Sets file to read cookies from
     * 
     * @param string $file
     * @throws FileNotFoundException
     * @throws Exception
     */
    public function setFileToRead(string $file): void
    {
        if (!file_exists($file)) {
            throw new FileNotFoundException($file);
        }
        if (!is_readable($file)) {
            throw new Exception("Cookies file not readable: ".$file);
        }
        \curl_setopt($this->connection, CURLOPT_COOKIEFILE, $file);
    }
    // file to write cookies to when closing handle
    
    /**
     * Sets file to write cookies to automatically after Request is destructed
     *
     * @param string $file
     * @throws FileNotFoundException
     * @throws Exception
     */
    public function setFileToWrite(string $file): void
    {
        if (!file_exists($file)) {
            throw new FileNotFoundException($file);
        }
        if (!is_writable($file)) {
            throw new Exception("Cookies file not writable: ".$file);
        }
        \curl_setopt($this->connection, CURLOPT_COOKIEJAR, $file);
    }
    
    /**
     * Deletes all cookies held in memory
     */
    public function deleteAll(): void
    {
        \curl_setopt($this->connection, CURLOPT_COOKIELIST, "ALL");
    }
    
    /**
     * Deletes only session cookies held in memory
     */
    public function deleteSession(): void
    {
        \curl_setopt($this->connection, CURLOPT_COOKIELIST, "SESS");
    }
    
    /**
     * Writes all known cookies to container file identified by setFileToWrite method
     */
    public function flushAll(): void
    {
        \curl_setopt($this->connection, CURLOPT_COOKIELIST, "FLUSH");
    }
    
    /**
     * Reloads all cookies from container file identified by setFileToRead method
     */
    public function reloadAll(): void
    {
        \curl_setopt($this->connection, CURLOPT_COOKIELIST, "RELOAD");
    }
    
    /**
     * Gets all cookies from handle.
     * 
     * @return string
     */
    public function getAll(): string
    {
        return \curl_getinfo($this->connection, CURLOPT_COOKIELIST);
    }
    
    /**
     * Adds cookie held in memory, to be written to container file identified by setFileToWrite method
     * 
     * @param Cookie $cookie
     */
    public function write(Cookie $cookie): void
    {
        \curl_setopt($this->connection, CURLOPT_COOKIELIST, "Set-Cookie: ".$cookie->toString());
    }
}

