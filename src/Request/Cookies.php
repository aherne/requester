<?php
namespace Lucinda\URL\Request;

use Lucinda\URL\Connection\Single as Connection;
use Lucinda\URL\FileNotFoundException;
use Lucinda\URL\Cookie;

/**
 * Encapsulates operations in working with request/response cookies
 */
class Cookies
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
     * Starts a new session cookie, ignoring that existing
     */
    public function startNewSession(): void
    {
        $this->connection->set(CURLOPT_COOKIESESSION, true);
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
        $this->connection->set(CURLOPT_COOKIEFILE, $file);
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
        $this->connection->set(CURLOPT_COOKIEJAR, $file);
    }
    
    /**
     * Adds cookie held in memory, to be written to container file identified by setFileToWrite method
     *
     * @param Cookie $cookie
     */
    public function write(Cookie $cookie): void
    {
        $this->connection->set(CURLOPT_COOKIELIST, $cookie->toString());
    }
    
    /**
     * Writes all known cookies to container file identified by setFileToWrite method
     */
    public function flushAll(): void
    {
        $this->connection->set(CURLOPT_COOKIELIST, "FLUSH");
    }
    
    /**
     * Reloads all cookies from container file identified by setFileToRead method
     */
    public function reloadAll(): void
    {
        $this->connection->set(CURLOPT_COOKIELIST, "RELOAD");
    }
    
    /**
     * Deletes only session cookies held in memory
     */
    public function deleteSession(): void
    {
        $this->connection->set(CURLOPT_COOKIELIST, "SESS");
    }
    
    /**
     * Deletes all cookies held in memory
     */
    public function deleteAll(): void
    {
        $this->connection->set(CURLOPT_COOKIELIST, "ALL");
    }
}

