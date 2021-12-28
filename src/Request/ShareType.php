<?php
namespace Lucinda\URL\Request;

/**
 * Enum encapsulating types of sharing to use in shared-requests
 */
enum ShareType : int
{
    case COOKIES = 2; // CURL_LOCK_DATA_COOKIE
    case DNS_CACHE = 3; // CURL_LOCK_DATA_DNS
    case SSL_SESSION = 4; // CURL_LOCK_DATA_SSL_SESSION
}
