<?php
namespace Lucinda\URL\Cookies;

/**
 * Encapsulates HTTP cookie operations
 */
class Cookie
{
    private $name;
    private $value;
    private $maxAge = 0;
    private $path = "/";
    private $domain;
    private $includeSubdomains = false;
    private $isSecuredByHTTPS = false;
    private $isSecuredByHTTPheaders = false;
    
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
     * @param bool $includeSubdomains
     */
    public function setDomain(string $domain, bool $includeSubdomains = false): void
    {
        $this->domain = $domain;
        $this->includeSubdomains = $includeSubdomains;
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
     * Gets whether or not subdomains should be available for cookie
     *
     * @return bool
     */
    public function getSubdomainsIncluded(): bool
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
     * Sets whether or not cookies are available only if protocol is HTTPS
     */
    public function setSecuredByHTTPS(): void
    {
        $this->isSecuredByHTTPS = true;
    }
    
    /**
     * Gets whether or not cookies are available only if protocol is HTTPS
     *
     * @return bool
     */
    public function getSecuredByHTTPS(): bool
    {
        return $this->isSecuredByHTTPS;
    }
    
    /**
     * Sets whether or not cookies are not available to client via JavaScript
     */
    public function setSecuredByHTTPheaders(): void
    {
        $this->isSecuredByHTTPheaders = true;
    }
    
    /**
     * Gets whether or not cookies are not available to client via JavaScript
     *
     * @return bool
     */
    public function getSecuredByHTTPheaders(): bool
    {
        return $this->isSecuredByHTTPheaders;
    }
}
