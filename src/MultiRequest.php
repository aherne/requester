<?php
namespace Lucinda\URL;

use Lucinda\URL\Request\Exception as RequestException;
use Lucinda\URL\Response\Exception as ResponseException;
use Lucinda\URL\Request\Pipelining;
use Lucinda\URL\Connection\Multi;

/**
 * Encapsulates a multi request, able to handle asynchronously multiple Request instances
 */
class MultiRequest
{
    private $connection;
    private $children = [];
    
    /**
     * Initiates a multi URL connection based on piplining options:
     * - DISABLED: connections are not pipelined
     * - HTTP1: attempts to pipeline HTTP/1.1 requests on connections that are already established
     * - HTTP2: attempts to multiplex the new transfer over an existing connection if HTTP/2
     * - HTTP1_HTTP2: attempts pipelining and multiplexing independently of each other
     *
     * @param Pipelining $pipeliningOption One of enum values (eg: Pipelining::HTTP2)
     */
    public function __construct(int $pipeliningOption = Pipelining::HTTP2)
    {
        $this->connection = new Multi();
        if ($pipeliningOption !== Pipelining::DISABLED) {
            $this->connection->set(CURLMOPT_PIPELINING, $pipeliningOption);
        }
    }
        
    /**
     * Adds request to be executed asynchronously
     *
     * @param Request $request
     */
    public function add(Request $request): void
    {
        $connection = $request->getConnection();
        $this->connection->add($connection);
        $this->children[(int) $connection->getDriver()] = $request;
    }
    
    /**
     * Sets obscure cURLm option not already covered by API.
     *
     * @param int $curlMultiOpt Curlmopt option key (eg: CURLMOPT_MAX_PIPELINE_LENGTH)
     * @param mixed $value
     * @throws RequestException If HTTP method is invalid
     */
    public function setCustomOption(int $curlMultiOpt, $value): void
    {
        if ($curlMultiOpt==CURLMOPT_PIPELINING) {
            throw new RequestException("Option already covered by constructor!");
        }
        $this->connection->set($curlMultiOpt, $value);
    }
    
    /**
     * Validates requests then executes them asynchronously in order to produce responses
     *
     * @param bool $returnTransfer Whether or not response body should be returned for each request
     * @param int $maxRedirectionsAllowed Maximum number of redirections allowed (if zero, it means none are) for each request
     * @param int $timeout Connection timeout in milliseconds for each request
     * @throws ResponseException If execution failed
     * @return Response[]
     */
    public function execute(bool $returnTransfer = true, int $maxRedirectionsAllowed = 0, int $timeout = 300000): array
    {
        // prepares handles for execution
        $headers =[];
        foreach ($this->children as $key=>$request) {
            $request->prepare($returnTransfer, $maxRedirectionsAllowed, $timeout);
            if ($returnTransfer) {
                $request->getConnection()->set(
                    CURLOPT_HEADERFUNCTION,
                    function ($curl, $header) use (&$headers, $key) {
                        $position = strpos($header, ":");
                        if ($position !== false) {
                            $headers[$key][ucwords(trim(substr($header, 0, $position)), "-")] = trim(substr($header, $position+1));
                        }
                        return strlen($header);
                    }
                );
            }
        }
                
        // executes multi-request and compiles responses
        $bodies = $this->connection->execute($headers, $returnTransfer);
        foreach ($this->children as $key=>$request) {
            $connection = $request->getConnection();
            if ($returnTransfer) {
                $responses[$key] = new Response($connection, $bodies[$key], $headers[$key]);
            } else {
                $responses[$key] = new Response($connection, "", []);
            }
        }
        
        return $responses;
    }
}
