<?php
namespace Lucinda\URL\Request;

use Lucinda\URL\FileNotFoundException;
use Lucinda\URL\Connection\Single as Connection;

/**
 * Encapsulates POST parameters to send in request
 */
class Parameters
{
    private array $parameters = [];
    private Connection $connection;
    
    /**
     * Sets connection to perform operations on. Optionally includes key-value set of POST parameters to add already.
     *
     * @param Connection $connection
     * @param array $parameters
     */
    public function __construct(Connection $connection, array $parameters = [])
    {
        $this->connection = $connection;
        if ($parameters) {
            $this->parameters = $parameters;
            $this->connection->set(CURLOPT_POSTFIELDS, $this->parameters);
        }
    }
    
    /**
     * Adds a POST parameter by key and value (to be accessible as $_POST in response)
     *
     * @param string $key
     * @param int|string|array $value
     */
    public function add(string $key, int|string|array $value): void
    {
        $this->parameters[$key] = $value;
        $this->connection->set(CURLOPT_POSTFIELDS, $this->parameters);
    }
    
    /**
     * Adds a POST parameter by key and file path/name (to be accessible as $_FILES in response)
     *
     * @param string $key
     * @param string $path
     * @param string $name
     * @throws FileNotFoundException
     */
    public function addFile(string $key, string $path, string $name = ""): void
    {
        if (!file_exists($path)) {
            throw new FileNotFoundException($path);
        }
        $this->parameters[$key] = $this->connection->createFile($path, $name);
        $this->connection->set(CURLOPT_POSTFIELDS, $this->parameters);
    }
}
