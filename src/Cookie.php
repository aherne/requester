<?php
namespace Lucinda\URL\Cookie;

/**
 * Encapsulates a HTTP cookie
 */
class Cookie
{
    private $name;
    private $value;
    private $maxAge;
    private $path;
    private $domain;
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
     */
    public function setDomain(string $domain): void
    {
        $this->domain = $domain;
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
     * Converts cookie to string ready to be value of Set-Cookie header
     * 
     * @return string
     */
    public function toString(): string
    {
        $output = "";
        if ($this->domain) {
            $output.="Domain: ".$this->domain."; ";
        }
        if ($this->path) {
            $output.="Path: ".$this->path."; ";
        }
        if ($this->maxAge) {
            $output.="Max-Age: ".$this->maxAge."; ";
        }
        if ($this->isSecuredByHTTPS) {
            $output.="Secure: 1; ";
        }
        if ($this->isSecuredByHTTPheaders) {
            $output.="HttpOnly: 1; ";
        }
        return $this->name."=".$this->value.($output?"; ".substr($output, 0, -2):"");
    }
}