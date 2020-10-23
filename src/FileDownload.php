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
        CURLOPT_POSTFIELDS=>"setRaw",
        CURLOPT_BUFFERSIZE=>"setProgressHandler",
        CURLOPT_NOPROGRESS=>"setProgressHandler",
        CURLOPT_PROGRESSFUNCTION=>"setProgressHandler"
    ];
    private $fileHandle;
    
    /**
     * {@inheritDoc}
     * @see \Lucinda\URL\Request::__destruct()
     */
    public function __destruct()
    {
        parent::__destruct();
        if ($this->fileHandle) {
            fclose($this->fileHandle);
        }
    }
    
    /**
     * {@inheritDoc}
     * @see \Lucinda\URL\Request::setMethod()
     */
    public function setMethod(string $method): void
    {
        if ($method != Method::GET) {
            throw new RequestException("Unsupported request method: ".$method);
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
        $this->fileHandle = fopen($path, "w");
        \curl_setopt($this->connection, CURLOPT_RETURNTRANSFER, true);
        \curl_setopt($this->connection, CURLOPT_FILE, $this->fileHandle);
    }
    
    /**
     * {@inheritDoc}
     * @see \Lucinda\URL\Request::setCustomOption()
     */
    public function setCustomOption(int $curlopt, $value): void
    {
        if (isset(self::ADDITIONAL_COVERED_OPTIONS[$curlopt])) {
            throw new RequestException("Option already covered by ".self::ADDITIONAL_COVERED_OPTIONS[$curlopt]." method!");
        } else if (isset(self::COVERED_OPTIONS[$curlopt])) {
            throw new RequestException("Option already covered by ".self::COVERED_OPTIONS[$curlopt]." method!");
        }
        \curl_setopt($this->connection, $curlopt, $value);
    }
    
    /**
     * Sets handler that will be used in tracking download progress
     *
     * @param Progress $progressHandler
     */
    public function setProgressHandler(Progress $progressHandler): void
    {
        \curl_setopt($this->connection, CURLOPT_BUFFERSIZE, $progressHandler->getBufferSize());
        \curl_setopt($this->connection, CURLOPT_NOPROGRESS, false);
        \curl_setopt($this->connection, CURLOPT_PROGRESSFUNCTION,
            function($curl, int $downloadSize, int $downloaded, int $uploadSize, int $uploaded) use ($progressHandler)
            {
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
        parent::prepare($returnTransfer, $maxRedirectionsAllowed, $timeout);
        
        // validate that handle was used
        if (!$this->fileHandle) {
            throw new RequestException("Download requests require usage of setFile method");
        }
    }
}
