<?php

namespace Lucinda\URL;

use Lucinda\URL\Connection\Single as Connection;
use Lucinda\URL\Request\Exception as RequestException;
use Lucinda\URL\Request\Proxy;
use Lucinda\URL\Response\Exception as ResponseException;
use Lucinda\URL\Request\Headers;
use Lucinda\URL\Request\SSL;
use Lucinda\URL\Request\Method;
use Lucinda\URL\Request\Parameters;

/**
 * Encapsulates a GET HTTP/HTTPS request for any resource
 *
 * TODO: add support for curl_copy_handle
 */
class Request
{
    protected const COVERED_OPTIONS = [
        CURLOPT_URL=>"setURL",
        CURLOPT_POST=>"setMethod",
        CURLOPT_NOBODY=>"setMethod",
        CURLOPT_CUSTOMREQUEST=>"setMethod",
        CURLOPT_COOKIESESSION=>"setCookies",
        CURLOPT_COOKIEFILE=>"setCookies",
        CURLOPT_COOKIEJAR=>"setCookies",
        CURLOPT_COOKIELIST=>"setCookies",
        CURLOPT_TIMECONDITION=>"setHeaders",
        CURLOPT_TIMEVALUE=>"setHeaders",
        CURLOPT_USERAGENT=>"setHeaders",
        CURLOPT_REFERER=>"setHeaders",
        CURLOPT_COOKIE=>"setHeaders",
        CURLOPT_HTTPHEADER=>"setHeaders",
        CURLOPT_POSTFIELDS=>"setParameters",
        CURLOPT_PROXY=>"setProxy",
        CURLOPT_PROXYUSERPWD=>"setProxy",
        CURLOPT_CAINFO=>"setSSL",
        CURLOPT_SSL_VERIFYPEER=>"setSSL",
        CURLOPT_SSL_VERIFYHOST=>"setSSL",
        CURLOPT_SSLCERT=>"setSSL",
        CURLOPT_SSLKEY=>"setSSL",
        CURLOPT_FOLLOWLOCATION=>"prepare",
        CURLOPT_HEADERFUNCTION=>"execute",
        CURLOPT_MAXREDIRS=>"prepare",
        CURLOPT_RETURNTRANSFER=>"prepare",
        CURLOPT_CONNECTTIMEOUT_MS=>"prepare"
    ];

    protected string $url = "";
    protected Method $method = Method::GET;

    protected bool $isSSL = false;
    protected bool $isPOST = false;
    protected bool $returnTransfer = true;
    protected Connection $connection;

    /**
     * Initiates a new URL connection or imports existing cURL handler
     *
     * @param  ?string $url
     * @throws RequestException
     */
    public function __construct(?string $url = null)
    {
        $this->connection = new Connection();
        if ($url) {
            $this->setURL($url);
        }
    }

    /**
     * Sets URL of requested resource
     *
     * @param  string $url
     * @throws RequestException If URL is invalid
     */
    public function setURL(string $url): void
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new RequestException("URL is invalid: ".$url);
        }
        $this->connection->setOption(CURLOPT_URL, $url);
        $this->url = $url;
    }

    /**
     * Sets HTTP method to use in requesting resource. If not set, GET is used by default!
     *
     * @param Method $method One of enum values (eg: Method::POST)
     */
    public function setMethod(Method $method): void
    {
        switch ($method) {
        case Method::GET:
            // do nothing
            break;
        case Method::POST:
            $this->connection->setOption(CURLOPT_POST, true);
            break;
        case Method::HEAD:
            $this->connection->setOption(CURLOPT_NOBODY, true);
            break;
        default:
            $this->connection->setOption(CURLOPT_CUSTOMREQUEST, $method->value);
            break;
        }
        $this->method = $method;
    }

    /**
     * Sets parameters to send in POST requests through Parameters object returned.
     *
     * @param  array<string,mixed> $parameters Optional key-value set of POST parameters to send already.
     * @return Parameters
     */
    public function setParameters(array $parameters = []): Parameters
    {
        $this->isPOST = true;
        return new Parameters($this->connection, $parameters);
    }

    /**
     * Sets proxy information
     *
     * @return Proxy
     */
    public function setProxy(): Proxy
    {
        return new Proxy($this->connection);
    }

    /**
     * Sets raw (binary) content to be uploaded using POST
     *
     * @param string $body
     */
    public function setRaw(string $body): void
    {
        $this->isPOST = true;
        $this->connection->setOption(CURLOPT_POSTFIELDS, $body);
    }

    /**
     * Sets HTTP headers to send through Headers object returned.
     *
     * @return Headers
     */
    public function setHeaders(): Headers
    {
        return new Headers($this->connection);
    }

    /**
     * Sets SQL policy through SSL object returned.
     *
     * @param  string $certificateAuthorityBundlePath
     * @return SSL
     * @throws FileNotFoundException
     */
    public function setSSL(string $certificateAuthorityBundlePath): SSL
    {
        $this->isSSL = true;
        return new SSL($this->connection, $certificateAuthorityBundlePath);
    }

    /**
     * Sets obscure CURLOPT not already covered by API.
     *
     * @param  int   $curlopt Curlopt option key (eg: CURLOPT_PRIVATE)
     * @param  mixed $value
     * @throws RequestException If option already covered
     */
    public function setCustomOption(int $curlopt, mixed $value): void
    {
        if (isset(self::COVERED_OPTIONS[$curlopt])) {
            throw new RequestException("Option already covered by ".self::COVERED_OPTIONS[$curlopt]." method!");
        }
        $this->connection->setOption($curlopt, $value);
    }

    /**
     * Sets whether transfer should be returned (default is YES)
     *
     * @param  bool $returnTransfer
     * @return void
     */
    public function setReturnTransfer(bool $returnTransfer): void
    {
        $this->returnTransfer = $returnTransfer;
    }

    /**
     * Gets connection object inside
     *
     * @return Connection
     */
    public function getConnection(): Connection
    {
        return $this->connection;
    }

    /**
     * Gets path to default (API-defined) certificate bundle
     *
     * @return string
     */
    protected function getDefaultCertificatePath(): string
    {
        return dirname(__DIR__).DIRECTORY_SEPARATOR."certificates".DIRECTORY_SEPARATOR."cacert.pem";
    }

    /**
     * Validates request and prepares it for being sent. Called already by "execute" method!
     *
     * @param  int $maxRedirectionsAllowed
     * @param  int $timeout
     * @return void
     * @throws FileNotFoundException
     * @throws RequestException
     */
    public function prepare(int $maxRedirectionsAllowed = 0, int $timeout = 300000): void
    {
        $this->validate();

        if (str_starts_with($this->url, "https") && !$this->isSSL) {
            $this->setSSL($this->getDefaultCertificatePath());
        }

        // sets redirection policy
        if ($maxRedirectionsAllowed==0) {
            $this->connection->setOption(CURLOPT_FOLLOWLOCATION, false);
        } else {
            $this->connection->setOption(CURLOPT_FOLLOWLOCATION, true);
            $this->connection->setOption(CURLOPT_MAXREDIRS, $maxRedirectionsAllowed);
        }

        // sets return transfer
        $this->connection->setOption(CURLOPT_RETURNTRANSFER, $this->returnTransfer);

        // sets connection timeout
        $this->connection->setOption(CURLOPT_CONNECTTIMEOUT_MS, $timeout);
    }

    /**
     * Validates request and throws exception when information inside is wrong or insufficient
     *
     * @return void
     * @throws RequestException
     */
    protected function validate(): void
    {
        // validate url
        if (!$this->url) {
            throw new RequestException("Setting a URL is mandatory!");
        }

        // validate POST parameters
        if ($this->method == Method::POST && !$this->isPOST) {
            throw new RequestException("No parameters to POST!");
        }
        if (!in_array($this->method, [Method::POST, Method::PUT, Method::DELETE]) && $this->isPOST) {
            throw new RequestException("Parameters can't be used unless request method is POST");
        }

        // validate SSL and sets certificate if missing
        if (!str_starts_with($this->url, "https") && $this->isSSL) {
            throw new RequestException("URL requested doesn't require SSL!");
        }
    }

    /**
     * Validates request then executes it in order to produce a response
     *
     * @param  int $maxRedirectionsAllowed Maximum number of redirections allowed (if zero, it means none are)
     * @param  int $timeout                Connection timeout in milliseconds
     * @throws ResponseException|RequestException|FileNotFoundException If execution failed
     * @return Response
     */
    public function execute(int $maxRedirectionsAllowed = 0, int $timeout = 300000): Response
    {
        // validates request and prepares it for being sent
        $this->prepare($maxRedirectionsAllowed, $timeout);

        // registers response header processing
        $headers = [];
        if ($this->returnTransfer) {
            $this->connection->setOption(
                CURLOPT_HEADERFUNCTION,
                function ($curl, $header) use (&$headers) {
                    $position = strpos($header, ":");
                    if ($position !== false) {
                        $name = ucwords(trim(substr($header, 0, $position)), "-");
                        $value = trim(substr($header, $position+1));
                        $headers[$name] = $value;
                    }
                    return strlen($header);
                }
            );
        }

        // executes request
        $startTime = microtime(true);
        $body = $this->connection->execute();
        $endTime = microtime(true);

        // split headers from body
        return new Response($this->connection, $body, $headers, ($endTime-$startTime));
    }
}
