<?php

namespace Lucinda\URL\Cookies;

/**
 * Encapsulates HTTP cookie operations
 */
class Cookie
{
    private string $name;
    private string $value;
    private int $maxAge = 0;
    private string $path = "/";
    private string $domain = "";
    private bool $includeSubdomains = false;
    private bool $isSecuredByHttps = false;
    private bool $isSecuredByHttpHeaders = false;

    /**
     * Sets up cookie by name and value
     *
     * @param string $name
     * @param string $value
     */
    public function __construct(string $name, string $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * Gets cookie name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Gets cookie value
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Sets path on the server in which the cookie will be available on.
     *
     * @param string $path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * Gets path on the server in which the cookie will be available on.
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Sets (sub)domain that the cookie is available to.
     *
     * @param string $domain
     */
    public function setDomain(string $domain): void
    {
        $this->domain = $domain;
    }

    /**
     * Gets domain that the cookie is available to.
     *
     * @return string|NULL
     */
    public function getDomain(): ?string
    {
        return $this->domain;
    }

    /**
     * Sets whether subdomains should be available
     *
     * @param  bool $includeSubdomains
     * @return void
     */
    public function setSubdomainsIncluded(bool $includeSubdomains): void
    {
        $this->includeSubdomains = $includeSubdomains;
    }

    /**
     * Gets whether subdomains should be available for cookie
     *
     * @return bool
     */
    public function isSubdomainsIncluded(): bool
    {
        return $this->includeSubdomains;
    }

    /**
     * Sets number of seconds by which cookie expires
     *
     * @param int $maxAge
     */
    public function setMaxAge(int $maxAge): void
    {
        $this->maxAge = $maxAge;
    }

    /**
     * Gets number of seconds by which cookie expires
     *
     * @return int
     */
    public function getMaxAge(): int
    {
        return $this->maxAge;
    }

    /**
     * Sets whether cookies are available only if protocol is Https
     */
    public function setSecuredByHttps(): void
    {
        $this->isSecuredByHttps = true;
    }

    /**
     * Gets whether cookies are available only if protocol is Https
     *
     * @return bool
     */
    public function isSecuredByHttps(): bool
    {
        return $this->isSecuredByHttps;
    }

    /**
     * Sets whether cookies are not available to client via JavaScript
     */
    public function setSecuredByHttpHeaders(): void
    {
        $this->isSecuredByHttpHeaders = true;
    }

    /**
     * Gets whether cookies are not available to client via JavaScript
     *
     * @return bool
     */
    public function isSecuredByHttpHeaders(): bool
    {
        return $this->isSecuredByHttpHeaders;
    }
}
