<?php

namespace Lucinda\URL\Request;

/**
 * Enum encapsulating pipelining options to use in multi-requests
 */
enum Pipelining: int
{
    case DISABLED = 0;
    case HTTP1 = 1; // CURLPIPE_HTTP1
    case HTTP2 = 2; // CURLPIPE_MULTIPLEX
    case HTTP1_HTTP2 = 3; // CURLPIPE_HTTP1_HTTP2
}
