<?php
namespace Lucinda\URL\Request;

/**
 * Enum encapsulating pipelining options to use in multi-requests
 */
interface Pipelining
{
    const DISABLED = 0;
    const HTTP1 = CURLPIPE_HTTP1;
    const HTTP2 = CURLPIPE_MULTIPLEX;
    const HTTP1_HTTP2 = 3;
}

