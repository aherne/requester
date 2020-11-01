<?php
namespace Lucinda\URL;

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
     */
    public function setFileToRead(string $file): void
    {
        if (!file_exists($file)) {
            throw new FileNotFoundException($file);
        }
        $this->connection->set(CURLOPT_COOKIEFILE, $file);
    }
    
    /**
     * Sets file to write cookies to automatically after Request is destructed
     *
     * @param string $file
     * @throws FileNotFoundException
     */
    public function setFileToWrite(string $file): void
    {
        if (!file_exists($file)) {
            throw new FileNotFoundException($file);
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
     * Gets all cookies in memory
     * 
     * @return Cookie[]
     */
    public function getAll(): array
    {        
        $temp = $this->connection->get(CURLINFO_COOKIELIST);
        $cookies = [];
        foreach($temp as $cookie) {
            $parts = explode("\t", $cookie);
            $cookie = new Cookie($parts[5], $parts[6]);
            if (stripos($parts[0], "#HttpOnly_")) {
                $cookie->setDomain(str_replace("#HttpOnly_", "", $parts[0]), ($parts[1]=="TRUE"));
                $cookie->setSecuredByHTTPheaders();
            } else {
                $cookie->setDomain($parts[0], ($parts[1]=="TRUE"));
            }
            $cookie->setPath($parts[2]);
            if ($parts[3] == "TRUE") {
                $cookie->setSecuredByHTTPheaders();
            }
            $cookie->setMaxAge((int) $parts[4]);
            $cookies[] = $cookie;
        }
        return $cookies;
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

