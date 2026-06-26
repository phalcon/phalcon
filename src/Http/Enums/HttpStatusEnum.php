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

namespace Phalcon\Http\Enums;

/**
 * Status Phrases trait
 */
enum HttpStatusEnum: int
{
    // Success - RFC 7231, 6.3.3
    case Accepted = 202;
    // Success - RFC 5842, 7.1
    case AlreadyReported = 208;
    // Server Error - RFC 7231, 6.6.3
    case BadGateway = 502;

    // Client Errors 4xx
    // Client Error - RFC 7231, 6.5.1
    case BadRequest = 400;
    // Client Error - RFC 7231, 6.5.8
    case Conflict = 409;
    // Informational 1xx
    // Information - RFC 7231, 6.2.1
    case Continue = 100;
    // Success - RFC 7231, 6.3.2
    case Created = 201;
    case EarlyHints = 103;
    // Client Error - RFC 7231, 6.5.14
    case ExpectationFailed = 417;
    // Client Error - RFC 4918, 11.4
    case FailedDependency = 424;
    // Client Error - RFC 7231, 6.5.3
    case Forbidden = 403;
    // Redirection - RFC 7231, 6.4.3
    case Found = 302;
    // Server Error - RFC 7231, 6.6.5
    case GatewayTimeout = 504;
    // Client Error - RFC 7231, 6.5.9
    case Gone = 410;
    // Client Error - RFC 7168, 2.3.3
    case ImATeapot          = 418;
    // Success - RFC 3229, 10.4.1
    case ImUsed = 226;
    // Server Error - RFC 4918, 11.5
    case InsufficientStorage = 507;

    // Server Errors 5xx
    // Server Error - RFC 7231, 6.6.1
    case InternalServerError = 500;
    // Client Error - RFC 7231, 6.5.10
    case LengthRequired = 411;
    // Client Error - RFC 4918, 11.3
    case Locked = 423;
    // Server Error - RFC 5842, 7.2
    case LoopDetected = 508;
    // Client Error - RFC 7231, 6.5.5
    case MethodNotAllowed = 405;
    case MisdirectedRequest = 421;
    // Redirection - RFC 7231, 6.4.2
    case MovedPermanently = 301;

    // Redirection 3xx
    // Redirection - RFC 7231, 6.4.1
    case MultipleChoices = 300;
    // Success - RFC 4918, 11.1
    case MultiStatus = 207;
    // Server Error - RFC 6585, 6
    case NetworkAuthenticationRequired = 511;
    // Success - RFC 7231, 6.3.5
    case NoContent = 204;
    // Success - RFC 7231, 6.3.4
    case NonAuthoritativeInformation = 203;
    // Client Error - RFC 7231, 6.5.6
    case NotAcceptable = 406;
    // Server Error - RFC 2774, 7
    case NotExtended = 510;
    // Client Error - RFC 7231, 6.5.4
    case NotFound = 404;
    // Server Error - RFC 7231, 6.6.2
    case NotImplemented = 501;
    // Redirection - RFC 7232, 4.1
    case NotModified = 304;

    // Successful 2xx
    // Success - RFC 7231, 6.3.1
    case OK = 200;
    // Success - RFC 7233, 4.1
    case PartialContent = 206;
    // Client Error - RFC 7231, 6.5.11
    case PayloadTooLarge = 413;
    // Client Error - RFC 7231, 6.5.2
    case PaymentRequired = 402;
    // Redirection - RFC 7538, 3
    case PermanentRedirect = 308;
    // Client Error - RFC 7232, 4.2
    case PreconditionFailed = 412;
    // Client Error - RFC 6585, 3
    case PreconditionRequired = 428;
    // Information - RFC 2518, 10.1
    case Processing = 102;            // RFC2518
    // Client Error - RFC 7235, 3.2
    case ProxyAuthenticationRequired = 407;
    // Client Error - RFC 7233, 4.4
    case RangeNotSatisfiable = 416;
    // Client Error - RFC 6585, 5
    case RequestHeaderFieldsTooLarge = 431;
    // Client Error - RFC 7231, 6.5.7
    case RequestTimeout = 408;
    // Redirection - RFC 7231, 6.4.6 (Deprecated)
    case Reserved = 306;              // Switch Proxy
    // Success - RFC 7231, 6.3.6
    case ResetContent = 205;
    // Redirection - RFC 7231, 6.4.4
    case SeeOther = 303;
    // Server Error - RFC 7231, 6.6.4
    case ServiceUnavailable = 503;
    // Information - RFC 7231, 6.2.2
    case SwitchingProtocols = 101;
    // Redirection - RFC 7231, 6.4.7
    case TemporaryRedirect = 307;

    case TooEarly = 425;
    // Client Error - RFC 6585, 4
    case TooManyRequests = 429;
    // Client Error - RFC 7235, 3.1
    case Unauthorized = 401;
    // Client Error - RFC 7725, 3
    case UnavailableForLegalReasons = 451;
    // Client Error - RFC 4918, 11.2
    case UnprocessableEntity = 422;
    // Client Error - RFC 7231, 6.5.13
    case UnsupportedMediaType = 415;
    // Client Error - RFC 7231, 6.5.15
    case UpgradeRequired = 426;
    // Client Error - RFC 7231, 6.5.12
    case UriTooLong = 414;
    // Redirection - RFC 7231, 6.4.5
    case UseProxy = 305;
    // Server Error - RFC 2295, 8.1
    case VariantAlsoNegotiates = 506;
    // Server Error - RFC 7231, 6.6.6
    case VersionNotSupported = 505;


    public function text(): string
    {
        return match ($this) {
            self::Continue                      => "Continue",
            self::SwitchingProtocols            => "Switching Protocols",
            self::Processing                    => "Processing",
            self::EarlyHints                    => "Early Hints",
            self::OK                            => "OK",
            self::Created                       => "Created",
            self::Accepted                      => "Accepted",
            self::NonAuthoritativeInformation   => "Non-Authoritative Information",
            self::NoContent                     => "No Content",
            self::ResetContent                  => "Reset Content",
            self::PartialContent                => "Partial Content",
            self::MultiStatus                   => "Multi-status",
            self::AlreadyReported               => "Already Reported",
            self::ImUsed                        => "IM Used",
            self::MultipleChoices               => "Multiple Choices",
            self::MovedPermanently              => "Moved Permanently",
            self::Found                         => "Found",
            self::SeeOther                      => "See Other",
            self::NotModified                   => "Not Modified",
            self::UseProxy                      => "Use Proxy",
            self::Reserved                      => "Switch Proxy",
            self::TemporaryRedirect             => "Temporary Redirect",
            self::PermanentRedirect             => "Permanent Redirect",
            self::BadRequest                    => "Bad Request",
            self::Unauthorized                  => "Unauthorized",
            self::PaymentRequired               => "Payment Required",
            self::Forbidden                     => "Forbidden",
            self::NotFound                      => "Not Found",
            self::MethodNotAllowed              => "Method Not Allowed",
            self::NotAcceptable                 => "Not Acceptable",
            self::ProxyAuthenticationRequired   => "Proxy Authentication Required",
            self::RequestTimeout                => "Request Time-out",
            self::Conflict                      => "Conflict",
            self::Gone                          => "Gone",
            self::LengthRequired                => "Length Required",
            self::PreconditionFailed            => "Precondition Failed",
            self::PayloadTooLarge               => "Request Entity Too Large",
            self::UriTooLong                    => "Request-URI Too Large",
            self::UnsupportedMediaType          => "Unsupported Media Type",
            self::RangeNotSatisfiable           => "Requested range not satisfiable",
            self::ExpectationFailed             => "Expectation Failed",
            self::ImATeapot                     => "I'm a teapot",
            self::MisdirectedRequest            => "Misdirected Request",
            self::UnprocessableEntity           => "Unprocessable Entity",
            self::Locked                        => "Locked",
            self::FailedDependency              => "Failed Dependency",
            self::TooEarly                      => "Unordered Collection",
            self::UpgradeRequired               => "Upgrade Required",
            self::PreconditionRequired          => "Precondition Required",
            self::TooManyRequests               => "Too Many Requests",
            self::RequestHeaderFieldsTooLarge   => "Request Header Fields Too Large",
            self::UnavailableForLegalReasons    => "Unavailable For Legal Reasons",
            self::InternalServerError           => "Internal Server Error",
            self::NotImplemented                => "Not Implemented",
            self::BadGateway                    => "Bad Gateway",
            self::ServiceUnavailable            => "Service Unavailable",
            self::GatewayTimeout                => "Gateway Time-out",
            self::VersionNotSupported           => "HTTP Version not supported",
            self::VariantAlsoNegotiates         => "Variant Also Negotiates",
            self::InsufficientStorage           => "Insufficient Storage",
            self::LoopDetected                  => "Loop Detected",
            self::NotExtended                   => "Not Extended",
            self::NetworkAuthenticationRequired => "Network Authentication Required",
        };
    }
}
