<?php
namespace Lucinda\URL\Cookies;

/**
 * Encapsulates cookie encryption/decryption for HTTP headers
 */
class CookieHeader implements CookieParser
{
    /**
     * {@inheritDoc}
     * @see \Lucinda\URL\Cookies\CookieParser::encrypt()
     */
    public function encrypt(Cookie $cookie): string
    {
        $options = "";
        if ($domain = $cookie->getDomain()) {
            $options.="Domain=".$domain."; ";
        }
        if ($path = $cookie->getPath()) {
            $options.="Path=".$path."; ";
        }
        if ($maxAge = $cookie->getMaxAge()) {
            $options.="Max-Age=".$maxAge."; ";
        }
        if ($cookie->getSecuredByHTTPS()) {
            $options.="Secure; ";
        }
        if ($cookie->getSecuredByHTTPheaders()) {
            $options.="HttpOnly; ";
        }
        return $cookie->getName()."=".$cookie->getValue().($options?"; ".substr($options, 0, -2):"");
    }

    /**
     * {@inheritDoc}
     * @see \Lucinda\URL\Cookies\CookieParser::decrypt()
     */
    public function decrypt(string $cookie): Cookie
    {
        $matches = [];
        preg_match("/^(__Secure-|__Host-)?([^=]+)=([^;]+);?(.*)?$/", $cookie, $matches);
        $cookie = new Cookie(trim($matches[2]), trim($matches[3]));
        if ($options = trim($matches[4])) {
            $matches = [];
            if (preg_match("/Domain\s*=\s*([^;]+)/", $options, $matches)) {
                $cookie->setDomain(trim($matches[1]));
            }
            
            $matches = [];
            if (preg_match("/Path\s*=\s*([^;]+)/", $options, $matches)) {
                $cookie->setPath(trim($matches[1]));
            }
            
            $matches = [];
            if (preg_match("/Max-Age\s*=\s*([^;]+)/", $options, $matches)) {
                $cookie->setMaxAge((int) trim($matches[1]));
            }
            
            if (strpos($options, "Secure")!==false) {
                $cookie->setSecuredByHTTPS();
            }
            
            if (strpos($options, "HttpOnly")) {
                $cookie->setSecuredByHTTPheaders();
            }
        }
        return $cookie;
    }
}
