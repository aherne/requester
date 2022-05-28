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
    private Multi $connection;
    /**
     * @var array<int,Request>
     */
    private array $children = [];
    protected bool $returnTransfer = true;

    /**
     * Initiates a multi URL connection based on piplining options:
     * - DISABLED: connections are not pipelined
     * - HTTP1: attempts to pipeline HTTP/1.1 requests on connections that are already established
     * - HTTP2: attempts to multiplex the new transfer over an existing connection if HTTP/2
     * - HTTP1_HTTP2: attempts pipelining and multiplexing independently of each other
     *
     * @param Pipelining $pipeliningOption One of enum values (eg: Pipelining::HTTP2)
     */
    public function __construct(Pipelining $pipeliningOption = Pipelining::HTTP2)
    {
        $this->connection = new Multi();
        $this->connection->setOption(CURLMOPT_PIPELINING, $pipeliningOption->value);
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
     * @param int|callable $value
     * @throws RequestException If HTTP method is invalid
     */
    public function setCustomOption(int $curlMultiOpt, int|callable $value): void
    {
        if ($curlMultiOpt==CURLMOPT_PIPELINING) {
            throw new RequestException("Option already covered by constructor!");
        }
        $this->connection->setOption($curlMultiOpt, $value);
    }

    /**
     * Sets whether transfer should be returned (default is YES)
     *
     * @param bool $returnTransfer
     * @return void
     */
    public function setReturnTransfer(bool $returnTransfer): void
    {
        $this->returnTransfer = $returnTransfer;
    }

    /**
     * Validates requests then executes them asynchronously in order to produce responses
     *
     * @param int $maxRedirectionsAllowed Maximum number of redirections allowed (if zero, it means none are) for each request
     * @param int $timeout Connection timeout in milliseconds for each request
     * @return Response[]
     */
    public function execute(int $maxRedirectionsAllowed = 0, int $timeout = 300000): array
    {
        $this->connection->setReturnTransfer($this->returnTransfer);

        // prepares handles for execution
        $headers =[];
        foreach ($this->children as $key=>$request) {
            $request->setReturnTransfer($this->returnTransfer);
            $request->prepare($maxRedirectionsAllowed, $timeout);
            if ($this->returnTransfer) {
                $request->getConnection()->setOption(
                    CURLOPT_HEADERFUNCTION,
                    function ($curl, $header) use (&$headers, $key) {
                        $position = strpos($header, ":");
                        if ($position !== false) {
                            $subKey = ucwords(trim(substr($header, 0, $position)), "-");
                            $subValue = trim(substr($header, $position+1));
                            $headers[$key][$subKey] = $subValue;
                        }
                        return strlen($header);
                    }
                );
            }
        }

        // executes multi-request and compiles responses
        $responses = [];
        $bodies = $this->connection->execute();
        foreach ($this->children as $key=>$request) {
            $connection = $request->getConnection();
            if ($this->returnTransfer) {
                $responses[$key] = new Response($connection, $bodies[$key], $headers[$key]);
            } else {
                $responses[$key] = new Response($connection, "", []);
            }
        }

        return $responses;
    }
}
