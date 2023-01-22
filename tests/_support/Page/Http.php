<?php

declare(strict_types=1);

namespace Page;

class Http
{
    public const HEADERS_SERVER = 'Server';
    public const HEADERS_STATUS = 'Status';

    public const HEADERS_CONTENT_ENCODING = 'Content-Encoding';
    public const HEADERS_CONTENT_ENCODING_GZIP = 'gzip';

    public const HEADERS_CONTENT_TYPE = 'Content-Type';
    public const HEADERS_CONTENT_TYPE_HTML = 'text/html';
    public const HEADERS_CONTENT_TYPE_HTML_CHARSET = 'text/html; charset=UTF-8';
    public const HEADERS_CONTENT_TYPE_HTML_RAW = 'Content-Type: text/html';
    public const HEADERS_CONTENT_TYPE_CSV = 'text/csv';
    public const HEADERS_CONTENT_TYPE_JSON = 'application/json';
    public const HEADERS_CONTENT_TYPE_PLAIN = 'text/plain';
    public const HEADERS_CONTENT_TYPE_PLAIN_RAW = 'Content-Type: text/plain';
    public const HEADERS_CONTENT_TYPE_XHTML_XML = 'application/xhtml+xml';

    public const HOST_LOCALHOST = 'localhost';

    public const REQUEST_METHOD_CONNECT = 'CONNECT';
    public const REQUEST_METHOD_DELETE = 'DELETE';
    public const REQUEST_METHOD_GET = 'GET';
    public const REQUEST_METHOD_HEAD = 'HEAD';
    public const REQUEST_METHOD_OPTIONS = 'OPTIONS';
    public const REQUEST_METHOD_PATCH = 'PATCH';
    public const REQUEST_METHOD_POST = 'POST';
    public const REQUEST_METHOD_PURGE = 'PURGE';
    public const REQUEST_METHOD_PUT = 'PUT';
    public const REQUEST_METHOD_TRACE = 'TRACE';

    public const STREAM = 'php://input';
    public const STREAM_MEMORY = 'php://memory';
    public const STREAM_NAME = 'php';
    public const STREAM_TEMP = 'php://temp';

    public const TEST_DOMAIN = 'phalcon.io';
    public const TEST_IP_IPV6 = '2a00:8640:1::224:36ff:feef:1d89';
    public const TEST_IP_ONE = '10.4.6.1';
    public const TEST_IP_TWO = '10.4.6.2';
    public const TEST_IP_THREE = '10.4.6.3';
    public const TEST_IP_MULTI = '10.4.6.4,10.4.6.5';
    public const TEST_URI = 'https://phalcon.io';
    public const TEST_USER_AGENT = 'Chrome/Other 1.0.0';
}
