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
    private mixed $fileHandle = null;

    /**
     * {@inheritDoc}
     *
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
     *
     * @throws RequestException
     * @see    \Lucinda\URL\Request::setMethod()
     */
    public function setMethod(Method $method): void
    {
        switch ($method) {
        case Method::POST:
            $this->connection->setOption(CURLOPT_POST, true);
            break;
        case Method::PUT:
            $this->connection->setOption(CURLOPT_PUT, true);
            break;
        default:
            throw new RequestException("Unsupported request method: ".$method->value);
        }
        $this->method = $method;
    }

    /**
     * Sets location of file to be uploaded using PUT
     *
     * @param  string $path
     * @throws FileNotFoundException
     */
    public function setFile(string $path): void
    {
        if (!file_exists($path)) {
            throw new FileNotFoundException($path);
        }
        $this->fileHandle = fopen($path, "r");
        $this->connection->setOption(CURLOPT_INFILE, $this->fileHandle);
        $this->connection->setOption(CURLOPT_INFILESIZE, filesize($path));
    }

    /**
     * {@inheritDoc}
     *
     * @throws RequestException
     * @see    \Lucinda\URL\Request::setParameters()
     */
    public function setParameters(array $parameters = []): Parameters
    {
        throw new RequestException("Using POST parameters for file upload is not allowed: please use setRaw method instead!");
    }

    /**
     * {@inheritDoc}
     *
     * @see \Lucinda\URL\Request::setCustomOption()
     */
    public function setCustomOption(int $curlopt, $value): void
    {
        if (isset(self::ADDITIONAL_COVERED_OPTIONS[$curlopt])) {
            throw new RequestException("Option already covered by ".self::ADDITIONAL_COVERED_OPTIONS[$curlopt]." method!");
        } elseif (isset(self::COVERED_OPTIONS[$curlopt])) {
            throw new RequestException("Option already covered by ".self::COVERED_OPTIONS[$curlopt]." method!");
        }
        $this->connection->setOption($curlopt, $value);
    }

    /**
     * Sets handler that will be used in tracking upload progress
     *
     * @param Progress $progressHandler
     */
    public function setProgressHandler(Progress $progressHandler): void
    {
        $this->connection->setOption(CURLOPT_BUFFERSIZE, $progressHandler->getBufferSize());
        $this->connection->setOption(CURLOPT_NOPROGRESS, false);
        $this->connection->setOption(
            CURLOPT_PROGRESSFUNCTION,
            function ($curl, int $downloadSize, int $downloaded, int $uploadSize, int $uploaded) use ($progressHandler) {
                $progressHandler->handle($uploadSize, $uploaded);
            }
        );
    }

    /**
     * {@inheritDoc}
     *
     * @see \Lucinda\URL\Request::prepare()
     */
    public function prepare(int $maxRedirectionsAllowed = 0, int $timeout = 300000): void
    {
        parent::prepare($maxRedirectionsAllowed, $timeout);

        // signals that an upload is pending
        if (!$this->isPOST) {
            $this->connection->setOption(CURLOPT_UPLOAD, true);
        }
    }

    /**
     * {@inheritDoc}
     *
     * @see \Lucinda\URL\Request::validate()
     */
    protected function validate(): void
    {
        parent::validate();

        // validate PUT transfer
        if ($this->method == Method::PUT && !$this->fileHandle) {
            throw new RequestException("PUT requests require usage of setFile method");
        }
        if ($this->method != Method::PUT && $this->fileHandle) {
            throw new RequestException("File handle requires PUT request method");
        }
    }
}
