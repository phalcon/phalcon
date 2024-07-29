<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Implementation of this file has been influenced by Nyholm/psr7 and Laminas
 *
 * @link    https://github.com/Nyholm/psr7
 * @license https://github.com/Nyholm/psr7/blob/master/LICENSE
 * @link    https://github.com/laminas/laminas-diactoros
 * @license https://github.com/zendframework/zend-diactoros/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Phalcon\Http\Message\Interfaces;

/**
 * Interface for Request methods
 *
 * Implementation of this file has been influenced by PHP FIG
 *
 * @link    https://github.com/php-fig/http-message-util/
 * @license https://github.com/php-fig/http-message-util/blob/master/LICENSE
 *
 * Defines constants for common HTTP status code.
 *
 * @see     https://tools.ietf.org/html/rfc2295#section-8.1
 * @see     https://tools.ietf.org/html/rfc2324#section-2.3
 * @see     https://tools.ietf.org/html/rfc2518#section-9.7
 * @see     https://tools.ietf.org/html/rfc2774#section-7
 * @see     https://tools.ietf.org/html/rfc3229#section-10.4
 * @see     https://tools.ietf.org/html/rfc4918#section-11
 * @see     https://tools.ietf.org/html/rfc5842#section-7.1
 * @see     https://tools.ietf.org/html/rfc5842#section-7.2
 * @see     https://tools.ietf.org/html/rfc6585#section-3
 * @see     https://tools.ietf.org/html/rfc6585#section-4
 * @see     https://tools.ietf.org/html/rfc6585#section-5
 * @see     https://tools.ietf.org/html/rfc6585#section-6
 * @see     https://tools.ietf.org/html/rfc7231#section-6
 * @see     https://tools.ietf.org/html/rfc7238#section-3
 * @see     https://tools.ietf.org/html/rfc7725#section-3
 * @see     https://tools.ietf.org/html/rfc7540#section-9.1.2
 * @see     https://tools.ietf.org/html/rfc8297#section-2
 * @see     https://tools.ietf.org/html/rfc8470#section-7
 */
interface ResponseStatusCodeInterface
{
    // Informational 1xx
    public const STATUS_ACCEPTED         = 202;
    public const STATUS_ALREADY_REPORTED = 208;
    public const STATUS_BAD_GATEWAY      = 502;
    public const STATUS_BAD_REQUEST      = 400;

    // Successful 2xx
    public const STATUS_BANDWIDTH_LIMIT_EXCEEDED             = 509;
    public const STATUS_BLOCKED_BY_WINDOWS_PARENTAL_CONTROLS = 450;
    public const STATUS_CLIENT_CLOSED_REQUEST                = 499;
    public const STATUS_CONFLICT                             = 409;
    public const STATUS_CONNECTION_TIMEOUT                   = 522;
    public const STATUS_CONTINUE                             = 100;
    public const STATUS_CREATED                              = 201;
    public const STATUS_EARLY_HINTS                          = 103;
    public const STATUS_EXPECTATION_FAILED                   = 417;
    public const STATUS_FAILED_DEPENDENCY                    = 424;

    // Redirection 3xx
    public const STATUS_FORBIDDEN                       = 403;
    public const STATUS_FOUND                           = 302;
    public const STATUS_GATEWAY_TIMEOUT                 = 504;
    public const STATUS_GONE                            = 410;
    public const STATUS_HTTP_REQUEST_SENT_TO_HTTPS_PORT = 497;
    public const STATUS_IM_A_TEAPOT                     = 418;
    public const STATUS_IM_USED                         = 226;
    public const STATUS_INSUFFICIENT_STORAGE            = 507;
    public const STATUS_INTERNAL_SERVER_ERROR           = 500;

    // Client Errors 4xx
    public const STATUS_INVALID_SSL_CERTIFICATE         = 526;
    public const STATUS_INVALID_TOKEN_ESRI              = 498;
    public const STATUS_LENGTH_REQUIRED                 = 411;
    public const STATUS_LOCKED                          = 423;
    public const STATUS_LOGIN_TIMEOUT                   = 440;
    public const STATUS_LOOP_DETECTED                   = 508;
    public const STATUS_METHOD_FAILURE                  = 420;
    public const STATUS_METHOD_NOT_ALLOWED              = 405;
    public const STATUS_MISDIRECTED_REQUEST             = 421;
    public const STATUS_MOVED_PERMANENTLY               = 301;
    public const STATUS_MULTIPLE_CHOICES                = 300;
    public const STATUS_MULTI_STATUS                    = 207;
    public const STATUS_NETWORK_AUTHENTICATION_REQUIRED = 511;
    public const STATUS_NETWORK_CONNECT_TIMEOUT_ERROR   = 599;
    public const STATUS_NETWORK_READ_TIMEOUT_ERROR      = 598;
    public const STATUS_NON_AUTHORITATIVE_INFORMATION   = 203;
    public const STATUS_NOT_ACCEPTABLE                  = 406;
    public const STATUS_NOT_EXTENDED                    = 510;
    public const STATUS_NOT_FOUND                       = 404;
    public const STATUS_NOT_IMPLEMENTED                 = 501;
    public const STATUS_NOT_MODIFIED                    = 304;
    public const STATUS_NO_CONTENT                      = 204;
    public const STATUS_NO_RESPONSE                     = 444;
    public const STATUS_OK                              = 200;
    public const STATUS_ORIGIN_DNS_ERROR                = 530;
    public const STATUS_ORIGIN_IS_UNREACHABLE           = 523;
    public const STATUS_PAGE_EXPIRED                    = 419;
    public const STATUS_PARTIAL_CONTENT                 = 206;
    public const STATUS_PAYLOAD_TOO_LARGE               = 413;

    // Server Errors 5xx
    public const STATUS_PAYMENT_REQUIRED                = 402;
    public const STATUS_PERMANENT_REDIRECT              = 308;
    public const STATUS_PRECONDITION_FAILED             = 412;
    public const STATUS_PRECONDITION_REQUIRED           = 428;
    public const STATUS_PROCESSING                      = 102;
    public const STATUS_PROXY_AUTHENTICATION_REQUIRED   = 407;
    public const STATUS_RAILGUN_ERROR                   = 527;
    public const STATUS_RANGE_NOT_SATISFIABLE           = 416;
    public const STATUS_REQUEST_HEADER_FIELDS_TOO_LARGE = 431;
    public const STATUS_REQUEST_HEADER_TOO_LARGE        = 494;
    public const STATUS_REQUEST_TIMEOUT                 = 408;

    // Unofficial
    public const STATUS_RESERVED                      = 306; // Unofficial - Apache Web Server
    public const STATUS_RESET_CONTENT                 = 205; // Unofficial - Laravel Framework
    public const STATUS_RETRY_WITH                    = 449; // Unofficial - Spring Framework
    public const STATUS_SEE_OTHER                     = 303; // Unofficial - IIS
    public const STATUS_SERVICE_UNAVAILABLE           = 503; // Unofficial - nginx
    public const STATUS_SSL_CERTIFICATE_ERROR         = 495; // Unofficial - IIS
    public const STATUS_SSL_CERTIFICATE_REQUIRED      = 496; // Unofficial - nginx
    public const STATUS_SSL_HANDSHAKE_FAILED          = 525; // Unofficial - nginx
    public const STATUS_SWITCHING_PROTOCOLS           = 101; // Unofficial - nginx
    public const STATUS_TEMPORARY_REDIRECT            = 307; // Unofficial - nginx
    public const STATUS_THIS_IS_FINE                  = 218; // Unofficial - nginx
    public const STATUS_TIMEOUT_OCCURRED              = 524; // Unofficial - ESRI
    public const STATUS_TOO_EARLY                     = 425; // Unofficial - nginx
    public const STATUS_TOO_MANY_REQUESTS             = 429; // Unofficial - Apache/cPanel
    public const STATUS_UNAUTHORIZED                  = 401; // Unofficial - Cloudflare
    public const STATUS_UNAVAILABLE_FOR_LEGAL_REASONS = 451; // Unofficial - Cloudflare
    public const STATUS_UNKNOWN_ERROR                 = 520; // Unofficial - Cloudflare
    public const STATUS_UNPROCESSABLE_ENTITY          = 422; // Unofficial - Cloudflare
    public const STATUS_UNSUPPORTED_MEDIA_TYPE        = 415; // Unofficial - Cloudflare
    public const STATUS_UPGRADE_REQUIRED              = 426; // Unofficial - Cloudflare
    public const STATUS_URI_TOO_LONG                  = 414; // Unofficial - Cloudflare
    public const STATUS_USE_PROXY                     = 305; // Unofficial - Cloudflare
    public const STATUS_VARIANT_ALSO_NEGOTIATES       = 506; // Unofficial - Cloudflare
    public const STATUS_VERSION_NOT_SUPPORTED         = 505; // Unofficial
    public const STATUS_WEB_SERVER_IS_DOWN            = 521; // Unofficial
}
