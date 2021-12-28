<?php
namespace Lucinda\URL;

use Lucinda\URL\Request\Exception as RequestException;
use Lucinda\URL\Request\Method;
use Lucinda\URL\Response\Progress;

/**
 * Encapsulates a file download via a GET HTTP/HTTPS request
 */
class FileDownload extends Request
{
    private const ADDITIONAL_COVERED_OPTIONS = [
        CURLOPT_FILE=>"setFile",
        CURLOPT_BUFFERSIZE=>"setProgressHandler",
        CURLOPT_NOPROGRESS=>"setProgressHandler",
        CURLOPT_PROGRESSFUNCTION=>"setProgressHandler"
    ];
    private $fileHandle;

    /**
     * {@inheritDoc}
     * @throws RequestException
     * @see \Lucinda\URL\Request::setMethod()
     */
    public function setMethod(Method $method): void
    {
        if ($method != Method::GET) {
            throw new RequestException("Unsupported request method: ".$method->value);
        }
        $this->method = $method;
    }
    
    /**
     * Sets location where file will be downloaded
     *
     * @param string $path
     */
    public function setFile(string $path): void
    {
        $this->fileHandle = fopen($path, "w+");
        $this->connection->set(CURLOPT_FILE, $this->fileHandle);
    }
    
    /**
     * {@inheritDoc}
     * @see \Lucinda\URL\Request::setCustomOption()
     */
    public function setCustomOption(int $curlopt, $value): void
    {
        if (isset(self::ADDITIONAL_COVERED_OPTIONS[$curlopt])) {
            throw new RequestException("Option already covered by ".self::ADDITIONAL_COVERED_OPTIONS[$curlopt]." method!");
        } elseif (isset(self::COVERED_OPTIONS[$curlopt])) {
            throw new RequestException("Option already covered by ".self::COVERED_OPTIONS[$curlopt]." method!");
        }
        $this->connection->set($curlopt, $value);
    }
    
    /**
     * Sets handler that will be used in tracking download progress
     *
     * @param Progress $progressHandler
     */
    public function setProgressHandler(Progress $progressHandler): void
    {
        $this->connection->set(CURLOPT_BUFFERSIZE, $progressHandler->getBufferSize());
        $this->connection->set(CURLOPT_NOPROGRESS, false);
        $this->connection->set(
            CURLOPT_PROGRESSFUNCTION,
            function ($curl, int $downloadSize, int $downloaded, int $uploadSize, int $uploaded) use ($progressHandler) {
                $progressHandler->handle($downloadSize, $downloaded);
            }
        );
    }
    
    /**
     * {@inheritDoc}
     * @see \Lucinda\URL\Request::prepare()
     */
    public function prepare(bool $returnTransfer = true, int $maxRedirectionsAllowed = 0, int $timeout = 300000): void
    {
        // validate url
        if (!$this->url) {
            throw new RequestException("Setting a URL is mandatory!");
        }
        
        // validate that handle was used
        if (!$this->fileHandle) {
            throw new RequestException("Download requests require usage of setFile method");
        }
        
        // validate SSL and sets certificate if missing
        if (!str_starts_with($this->url, "https") && $this->isSSL) {
            throw new RequestException("URL requested doesn't require SSL!");
        }
        if (str_starts_with($this->url, "https") && !$this->isSSL) {
            $this->setSSL(dirname(__DIR__).DIRECTORY_SEPARATOR."certificates".DIRECTORY_SEPARATOR."cacert.pem");
        }
        
        // ignore non-applicable $returntransfer, $maxRedirectionsAllowed
        
        // sets connection timeout
        $this->connection->set(CURLOPT_CONNECTTIMEOUT_MS, $timeout);
    }

    /**
     * {@inheritDoc}
     * @see \Lucinda\URL\Request::execute()
     */
    public function execute(bool $returnTransfer = true, int $maxRedirectionsAllowed = 0, int $timeout = 300000): Response
    {
        $response = parent::execute($returnTransfer, $maxRedirectionsAllowed, $timeout);
        fclose($this->fileHandle);
        return $response;
    }
}
