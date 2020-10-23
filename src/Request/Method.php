<?php
namespace Lucinda\URL\Request;

/**
 * Enum encapsulating HTTP request methods
 */
interface Method
{
    const GET = "GET";
    const POST = "POST";
    const PUT = "PUT";
    const DELETE = "DELETE";
    const HEAD = "HEAD";
    const OPTIONS = "OPTIONS";
    const CONNECT = "CONNECT";
    const TRACE = "TRACE";
    const PATCH = "PATCH";
}

