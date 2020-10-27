<?php
namespace Lucinda\URL;

/**
 * Encapsulates a HTTP cookie
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
     * Sets path on the server in which the cookie will be available on.
     *
     * @param string $path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
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
     * Sets number of seconds by which cookie expires
     * 
     * @param int $maxAge
     */
    public function setMaxAge(int $maxAge): void
    {
        $this->maxAge = $maxAge;
    }
    
    /**
     * Sets whether or not cookies are available only if protocol is HTTPS
     */
    public function setSecuredByHTTPS(): void
    {
        $this->isSecuredByHTTPS = true;
    }
    
    /**
     * Sets whether or not cookies are not available to client via JavaScript
     */
    public function setSecuredByHTTPheaders(): void
    {
        $this->isSecuredByHTTPheaders = true;
    }
    
    /**
     * Converts cookie to string ready to be placed as new line in COOKIEJAR file
     * 
     * @return string
     */
    public function toString(): string
    {
        $options = [];
        $options[] = ($this->domain?$this->domain:"localhost").($this->isSecuredByHTTPheaders?"#HttpOnly_":"");
        $options[] = $this->includeSubdomains?"TRUE":"FALSE";
        $options[] = $this->path?$this->path:"/";
        $options[] = $this->isSecuredByHTTPS?"true":"false";
        $options[] = $this->maxAge?$this->maxAge:0;
        $options[] = $this->name;
        $options[] = $this->value;
        return implode("\t", $options);
    }
    
    /**
     * Converts cookie to string ready to be value of Set-Cookie header
     *
     * @return string
     */
    public function toHeader(): string
    {
        $options = "";
        if ($this->domain) {
            $options.="Domain=".$this->domain."; ";
        }
        if ($this->path) {
            $options.="Path=".$this->path."; ";
        }
        if ($this->maxAge) {
            $options.="Max-Age=".$this->maxAge."; ";
        }
        if ($this->isSecuredByHTTPS) {
            $options.="Secure; ";
        }
        if ($this->isSecuredByHTTPheaders) {
            $options.="HttpOnly; ";
        }
        return $this->name."=".$this->value.($options?"; ".substr($options, 0, -2):"");
    }
}