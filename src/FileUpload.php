<?php
namespace Lucinda\URL;

use Lucinda\URL\Request\Exception as RequestException;
use Lucinda\URL\Request\Method;
use Lucinda\URL\Response\Progress;
use Lucinda\URL\Request\Parameters;

/**
 * Encapsulates a file upload via a POST/PUT HTTP/HTTPS request
 */
class FileUpload extends Request
{
    private const ADDITIONAL_COVERED_OPTIONS = [
        CURLOPT_INFILE=>"setFile",
        CURLOPT_INFILESIZE=>"setFile",
        CURLOPT_PUT=>"setMethod",
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
        if ($this->fileHandle) {
            fclose($this->fileHandle);
        }
    }

    /**
     * {@inheritDoc}
     * @throws RequestException
     * @see \Lucinda\URL\Request::setMethod()
     */
    public function setMethod(Method $method): void
    {
        switch ($method) {
            case Method::POST:
                $this->connection->set(CURLOPT_POST, true);
                break;
            case Method::PUT:
                $this->connection->set(CURLOPT_PUT, true);
                break;
            default:
                throw new RequestException("Unsupported request method: ".$method->value);
                break;
        }
        $this->method = $method;
    }
    
    /**
     * Sets location of file to be uploaded using PUT
     *
     * @param string $path
     * @throws FileNotFoundException
     */
    public function setFile(string $path): void
    {
        if (!file_exists($path)) {
            throw new FileNotFoundException($path);
        }
        $this->fileHandle = fopen($path, "r");
        $this->connection->set(CURLOPT_INFILE, $this->fileHandle);
        $this->connection->set(CURLOPT_INFILESIZE, filesize($path));
    }

    /**
     * {@inheritDoc}
     * @throws RequestException
     * @see \Lucinda\URL\Request::setParameters()
     */
    public function setParameters(array $parameters = []): Parameters
    {
        throw new RequestException("Using POST parameters for file upload is not allowed: please use setRaw method instead!");
    }
    
    /**
     * Sets raw (binary) content to be uploaded using POST
     *
     * @param string $body
     */
    public function setRaw(string $body): void
    {
        $this->isPOST = true;
        $this->connection->set(CURLOPT_POSTFIELDS, $body);
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
     * Sets handler that will be used in tracking upload progress
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
                $progressHandler->handle($uploadSize, $uploaded);
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
        
        // validate PUT transfer
        if ($this->method == Method::PUT && !$this->fileHandle) {
            throw new RequestException("PUT requests require usage of setFile method");
        }
        if ($this->method != Method::PUT && $this->fileHandle) {
            throw new RequestException("File handle requires PUT request method");
        }
        
        // signals that an upload is pending
        if (!$this->isPOST) {
            $this->connection->set(CURLOPT_UPLOAD, true);
        }
    }
}
