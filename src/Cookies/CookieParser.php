<?php

namespace Lucinda\URL\Cookies;

/**
 * Defines a blueprint for encrypting/decrypting cookie info
 */
interface CookieParser
{
    /**
     * Encrypts cookie
     *
     * @param  Cookie $cookie
     * @return string
     */
    public function encrypt(Cookie $cookie): string;

    /**
     * Decrypts cookie
     *
     * @param  string $cookie
     * @return Cookie
     */
    public function decrypt(string $cookie): Cookie;
}
