<?php
namespace Lucinda\URL\Cookies;

/**
 * Encapsulates cookie encryption/decryption for CURLOPT_COOKIEFILE and CURLOPT_COOKIEJAR files
 */
class CookieFile implements CookieParser
{
    /**
     * {@inheritDoc}
     * @see \Lucinda\URL\Cookies\CookieParser::encrypt()
     */
    public function encrypt(Cookie $cookie): string
    {
        $options = [];
        $options[] = ($cookie->getDomain()??"localhost").($cookie->getSecuredByHTTPheaders()?"#HttpOnly_":"");
        $options[] = $cookie->getSubdomainsIncluded()?"TRUE":"FALSE";
        $options[] = $cookie->getPath();
        $options[] = $cookie->getSecuredByHTTPS()?"TRUE":"FALSE";
        $options[] = $cookie->getMaxAge();
        $options[] = $cookie->getName();
        $options[] = $cookie->getValue();
        return implode("\t", $options);
    }

    /**
     * {@inheritDoc}
     * @see \Lucinda\URL\Cookies\CookieParser::decrypt()
     */
    public function decrypt(string $cookie): Cookie
    {
        $parts = explode("\t", $cookie);
        $cookie = new Cookie($parts[5], $parts[6]);
        if (stripos($parts[0], "#HttpOnly_")) {
            $cookie->setDomain(str_replace("#HttpOnly_", "", $parts[0]), ($parts[1]=="TRUE"));
            $cookie->setSecuredByHTTPheaders();
        } else {
            $cookie->setDomain($parts[0], ($parts[1]=="TRUE"));
        }
        $cookie->setPath($parts[2]);
        if ($parts[3] == "TRUE") {
            $cookie->setSecuredByHTTPs();
        }
        $cookie->setMaxAge((int) $parts[4]);
        return $cookie;
    }
}
