<?php
namespace Lucinda\URL;

use Lucinda\URL\Request\Exception as RequestException;
use Lucinda\URL\Request\Method;
use Lucinda\URL\Response\Progress;

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
        CURLOPT_BUFFERSIZE=>"upload",
        CURLOPT_NOPROGRESS=>"upload",
        CURLOPT_PROGRESSFUNCTION=>"upload",
        CURLOPT_RETURNTRANSFER=>"upload",
        CURLOPT_CONNECTTIMEOUT_MS=>"upload"
    ];
    private $fileHandle;
    private $isRawTransfer = false;
    
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
        switch($method)
        {
            case Method::POST:
                \curl_setopt($this->connection, CURLOPT_POST, true);
                break;
            case Method::PUT:
                \curl_setopt($this->connection, CURLOPT_PUT, true);
                break;
            default:
                throw new RequestException("Unsupported request method: ".$method);
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
        \curl_setopt($this->connection, CURLOPT_INFILE, $this->fileHandle);
        \curl_setopt($this->connection, CURLOPT_INFILESIZE, filesize($path));
    }
    
    /**
     * Sets raw (binary) content to be uploaded using POST
     * 
     * @param string $body
     */
    public function setRaw(string $body): void
    {
        $this->isRawTransfer = true;
        \curl_setopt($this->connection, CURLOPT_POSTFIELDS, $body);
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
     * {@inheritDoc}
     * @see \Lucinda\URL\Request::validate()
     */
    protected function validate(): void
    {
        // validate url
        if (!$this->url) {
            throw new RequestException("Setting a URL is mandatory!");
        }
        
        // validate PUT transfer
        if ($this->method == Method::PUT && !$this->fileHandle) {
            throw new RequestException("PUT requests require usage of setFile method");
        }
        
        // validate POST transfer
        if ($this->method == Method::POST && !$this->isRawTransfer) {
            throw new RequestException("No parameters or raw body to POST!");
        }
        
        // validate SSL
        if (strpos($this->url, "https")!==0 && $this->isSSL) {
            throw new RequestException("URL requested doesn't require SSL!");
        }
    }
    
    /**
     * Sets handler that will be used in tracking upload progress
     *
     * @param Progress $progressHandler
     */
    private function setProgressHandler(Progress $progressHandler): void
    {
        \curl_setopt($this->connection, CURLOPT_BUFFERSIZE, $progressHandler->getBufferSize());
        \curl_setopt($this->connection, CURLOPT_NOPROGRESS, false);
        \curl_setopt($this->connection, CURLOPT_PROGRESSFUNCTION,
            function($curl, int $downloadSize, int $downloaded, int $uploadSize, int $uploaded) use ($progressHandler)
            {
                $progressHandler->handle($uploadSize, $uploaded);
            }
        );
    }    
    
    /**
     * Executes request and uploads file
     *
     * @param Progress $progressHandler Handler to use in tracking upload progress.
     * @param int $timeout Connection timeout in milliseconds
     * @return Response
     */
    public function upload(Progress $progressHandler = null, int $timeout = 300000): Response
    {
        $this->validate();
        
        // use default certificate if none given
        if (strpos($this->url, "https")===0 && !$this->isSSL) {
            $this->setSSL(dirname(__DIR__).DIRECTORY_SEPARATOR."cacert.pem");
        }
        
        // delegates upload progress to handler
        if($progressHandler !== null) {
            $this->setProgressHandler($progressHandler);
        }
        
        // signals an upload is pending
        \curl_setopt($this->connection, CURLOPT_UPLOAD, true);
        
        // sets transfer to be always returned
        \curl_setopt($this->connection, CURLOPT_RETURNTRANSFER, true);
        
        // sets connection timeout
        \curl_setopt($this->connection, CURLOPT_CONNECTTIMEOUT_MS, $timeout);
                
        return $this->execute();
    }
}
