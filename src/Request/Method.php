<?php
namespace Lucinda\URL\Request;

/**
 * Enum encapsulating HTTP request methods
 */
enum Method : string
{
    case GET = "GET";
    case POST = "POST";
    case PUT = "PUT";
    case DELETE = "DELETE";
    case HEAD = "HEAD";
    case OPTIONS = "OPTIONS";
    case CONNECT = "CONNECT";
    case TRACE = "TRACE";
    case PATCH = "PATCH";
}
