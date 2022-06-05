<?php

namespace Lucinda\URL;

use Lucinda\URL\Response\Exception as ResponseException;
use Lucinda\URL\Connection\Single as Connection;

/**
 * Encapsulates all information about response received
 */
class Response
{
    private const COVERED_OPTIONS = [
        CURLINFO_TOTAL_TIME=>"setDuration",
        CURLINFO_RESPONSE_CODE=>"setStatusCode",
        CURLINFO_EFFECTIVE_URL=>"setURL"
    ];

    private Connection $connection;

    private string $url;
    private int $duration;
    private int $responseCode;
    private string $body;
    /**
     * @var array<string,string>
     */
    private array $headers;

    /**
     * Sets basic information, response body and headers
     *
     * @param Connection           $connection
     * @param string               $body
     * @param array<string,string> $headers
     * @param float                $duration
     */
    public function __construct(Connection $connection, string $body, array $headers, float $duration = 0)
    {
        $this->connection = $connection;

        $this->setDuration($duration);
        $this->setStatusCode();
        $this->setURL();
        $this->setBody($body);
        $this->setHeaders($headers);
    }

    /**
     * Gets obscure CURLINFO not already covered by API.
     *
     * @param  int $curlinfo Curlinfo option (eg: CURLINFO_PRIVATE)
     * @return mixed
     * @throws ResponseException If option already covered
     */
    public function getCustomOption(int $curlinfo): mixed
    {
        if (isset(self::COVERED_OPTIONS[$curlinfo])) {
            throw new ResponseException("Option already covered by ".self::COVERED_OPTIONS[$curlinfo]." method!");
        }
        return $this->connection->getOption($curlinfo);
    }

    /**
     * Sets total response duration in milliseconds
     *
     * @param float $duration Total duration calculated by PHP
     */
    private function setDuration(float $duration): void
    {
        if ($duration==0) {
            $this->duration = (int) round($duration*1000);
        } elseif (defined("CURLINFO_TOTAL_TIME_T")) {
            $this->duration = (int) round($this->connection->getOption(CURLINFO_TOTAL_TIME_T)/1000);
        } else {
            $this->duration = (int) round($this->connection->getOption(CURLINFO_TOTAL_TIME)*1000);
        }
    }

    /**
     * Gets total duration in milliseconds by whom response was received
     *
     * @return int
     */
    public function getDuration(): int
    {
        return $this->duration;
    }

    /**
     * Sets response HTTP status code
     */
    private function setStatusCode(): void
    {
        $this->responseCode = $this->connection->getOption(CURLINFO_RESPONSE_CODE);
    }

    /**
     * Gets response HTTP status code
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->responseCode;
    }

    /**
     * Sets URL requested
     */
    private function setURL(): void
    {
        $this->url = $this->connection->getOption(CURLINFO_EFFECTIVE_URL);
    }

    /**
     * Gets url requested
     *
     * @return string
     */
    public function getURL(): string
    {
        return $this->url;
    }

    /**
     * Sets response body
     *
     * @param string $body
     */
    private function setBody(string $body): void
    {
        $this->body = $body;
    }

    /**
     * Gets response body
     *
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * Sets response headers by name and value
     *
     * @param array<string,string> $headers
     */
    private function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }

    /**
     * Gets response headers by name and value
     *
     * @return array<string,string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
