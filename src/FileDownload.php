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
    private mixed $fileHandle = null;

    /**
     * {@inheritDoc}
     *
     * @throws RequestException
     * @see    \Lucinda\URL\Request::setMethod()
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
        $this->connection->setOption(CURLOPT_FILE, $this->fileHandle);
    }

    /**
     * Sets raw (binary) content to be uploaded using POST
     *
     * @param string $body
     */
    public function setRaw(string $body): void
    {
        // method not applicable
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
     * Sets handler that will be used in tracking download progress
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
                $progressHandler->handle($downloadSize, $downloaded);
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
        $this->validate();

        // validate that handle was used
        if (str_starts_with($this->url, "https") && !$this->isSSL) {
            $this->setSSL($this->getDefaultCertificatePath());
        }

        // ignore non-applicable $returntransfer, $maxRedirectionsAllowed

        // sets connection timeout
        $this->connection->setOption(CURLOPT_CONNECTTIMEOUT_MS, $timeout);
    }

    /**
     * {@inheritDoc}
     *
     * @see \Lucinda\URL\Request::validate()
     */
    protected function validate(): void
    {
        // validate url
        if (!$this->url) {
            throw new RequestException("Setting a URL is mandatory!");
        }

        if (!$this->fileHandle) {
            throw new RequestException("Download requests require usage of setFile method");
        }

        // validate SSL and sets certificate if missing
        if (!str_starts_with($this->url, "https") && $this->isSSL) {
            throw new RequestException("URL requested doesn't require SSL!");
        }
    }

    /**
     * {@inheritDoc}
     *
     * @see \Lucinda\URL\Request::execute()
     */
    public function execute(int $maxRedirectionsAllowed = 0, int $timeout = 300000): Response
    {
        $response = parent::execute($maxRedirectionsAllowed, $timeout);
        fclose($this->fileHandle);
        return $response;
    }
}
