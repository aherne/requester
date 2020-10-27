<?php
namespace Lucinda\URL\Request;

/**
 * Enum encapsulating types of sharing to use in shared-requests
 */
interface ShareType
{
    const COOKIES = CURL_LOCK_DATA_COOKIE;
    const DNS_CACHE = CURL_LOCK_DATA_DNS;
    const SSL_SESSION = CURL_LOCK_DATA_SSL_SESSION;
}
