<?php
namespace Lucinda\URL;

use Lucinda\URL\Request\Exception as RequestException;
use Lucinda\URL\Response\Exception as ResponseException;
use Lucinda\URL\Response\Information;
use Lucinda\URL\Request\Pipelining;

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
    public function __construct(int $pipeliningOption = Pipelining::HTTP1_HTTP2)
    {
        $this->connection = \curl_multi_init();
        \curl_multi_setopt($this->connection, CURLMOPT_PIPELINING, $pipeliningOption);
    }
    
    /**
     * Closes all open handles automatically
     */
    public function __destruct()
    {
        foreach($this->children as $child) {
            \curl_multi_remove_handle($this->connection, $child->getDriver());
        }
        \curl_multi_close($this->connection);
    }
    
    /**
     * Adds request to be executed asynchronously
     * 
     * @param Request $request
     */
    public function add(Request $request): void
    {
        $driver = $request->getDriver();
        \curl_multi_add_handle($this->connection, $driver);
        $this->children[(int) $driver] = $request;
        return $request;
    }
    
    /**
     * Sets obscure cURLm option not already covered by API.
     *
     * @param int $curlopt Curlmopt option key (eg: CURLMOPT_MAX_PIPELINE_LENGTH)
     * @param mixed $value
     * @throws RequestException If HTTP method is invalid
     */
    public function setCustomOption(int $curlMultiOpt, $value): void
    {
        if ($curlMultiOpt==CURLMOPT_PIPELINING) {
            throw new RequestException("Option already covered by constructor!");
        }
        \curl_multi_setopt($this->connection, $curlMultiOpt, $value);
    }
    
    /**
     * Validates requests then executes them asynchronously in order to produce responses
     * 
     * @param int $returnTransfer Whether or not response body should be returned for each request
     * @param int $maxRedirectionsAllowed Maximum number of redirections allowed (if zero, it means none are) for each request
     * @param int $timeout Connection timeout in milliseconds for each request
     * @throws ResponseException If execution failed
     * @return Response[]
     */
    public function execute(bool $returnTransfer = true, int $maxRedirectionsAllowed = 0, int $timeout = 300000): array
    {
        // prepares handles for execution
        $headers =[]; 
        foreach($this->children as $key=>$request) {
            $request->prepare($returnTransfer, $maxRedirectionsAllowed, $timeout);
            if ($returnTransfer) {
                \curl_setopt($request->getDriver(), CURLOPT_HEADERFUNCTION,
                    function($curl, $header) use (&$headers, $key)
                    {
                        $len = strlen($header);
                        $header = explode(':', $header, 2);
                        // ignore invalid headers
                        if (count($header) < 2) {
                            return $len;
                        } else {
                            $headers[$key][strtolower(trim($header[0]))][] = trim($header[1]);
                            return $len;
                        }
                    }
                );
            }
        }
                
        // executes multi handle
        $active = null;
        do {
            $status = curl_multi_exec($this->connection, $active);
            if ($status !== CURLM_OK) {
                throw new ResponseException(curl_multi_strerror($this->connection), curl_multi_errno($this->connection));
            }
            if ($active) {
                curl_multi_select($this->connection);
            }
        } while ($active);
        
        // get responses
        $responses = [];
        $i = 0;
        while ($info = curl_multi_info_read($this->connection)) {
            if ($info["result"]!=CURLE_OK) {
                throw new ResponseException(curl_multi_strerror($this->connection), curl_multi_errno($this->connection));
            }
            $key = (int) $info['handle'];
            $driver = $this->children[$key]->getDriver();
            if ($returnTransfer) {
                $responses[$key] = new Response(new Information($driver, 0), curl_multi_getcontent($driver), $headers[$key]);
            } else {
                $responses[$key] = new Response(new Information($driver, 0), "", []);
            }
            $i++;
        }
        return $responses;
    }
}

