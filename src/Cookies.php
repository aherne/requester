<?php
namespace Lucinda\URL;

use Lucinda\URL\Connection\Single as Connection;
use Lucinda\URL\Cookies\Cookie;
use Lucinda\URL\Cookies\CookieFile;

/**
 * Encapsulates operations in working with request/response cookies
 */
class Cookies
{
    private Connection $connection;
    
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
        $cookieFile = new CookieFile();
        $this->connection->set(CURLOPT_COOKIELIST, $cookieFile->encrypt($cookie));
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
        foreach ($temp as $cookie) {
            $cookieFile = new CookieFile();
            $cookies[] = $cookieFile->decrypt($cookie);
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
