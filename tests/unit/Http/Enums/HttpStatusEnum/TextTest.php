<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Http\Enums\HttpStatusEnum;

use Phalcon\Http\Enums\HttpStatusEnum;
use Phalcon\Tests\AbstractUnitTestCase;

final class TextTest extends AbstractUnitTestCase
{
    /**
     * @return array<array{HttpStatusEnum, string}>
     */
    public static function getExamples(): array
    {
        return [
            [HttpStatusEnum::Continue,                      'Continue'],
            [HttpStatusEnum::SwitchingProtocols,            'Switching Protocols'],
            [HttpStatusEnum::Processing,                    'Processing'],
            [HttpStatusEnum::EarlyHints,                    'Early Hints'],
            [HttpStatusEnum::OK,                            'OK'],
            [HttpStatusEnum::Created,                       'Created'],
            [HttpStatusEnum::Accepted,                      'Accepted'],
            [HttpStatusEnum::NonAuthoritativeInformation,   'Non-Authoritative Information'],
            [HttpStatusEnum::NoContent,                     'No Content'],
            [HttpStatusEnum::ResetContent,                  'Reset Content'],
            [HttpStatusEnum::PartialContent,                'Partial Content'],
            [HttpStatusEnum::MultiStatus,                   'Multi-status'],
            [HttpStatusEnum::AlreadyReported,               'Already Reported'],
            [HttpStatusEnum::ImUsed,                        'IM Used'],
            [HttpStatusEnum::MultipleChoices,               'Multiple Choices'],
            [HttpStatusEnum::MovedPermanently,              'Moved Permanently'],
            [HttpStatusEnum::Found,                         'Found'],
            [HttpStatusEnum::SeeOther,                      'See Other'],
            [HttpStatusEnum::NotModified,                   'Not Modified'],
            [HttpStatusEnum::UseProxy,                      'Use Proxy'],
            [HttpStatusEnum::Reserved,                      'Switch Proxy'],
            [HttpStatusEnum::TemporaryRedirect,             'Temporary Redirect'],
            [HttpStatusEnum::PermanentRedirect,             'Permanent Redirect'],
            [HttpStatusEnum::BadRequest,                    'Bad Request'],
            [HttpStatusEnum::Unauthorized,                  'Unauthorized'],
            [HttpStatusEnum::PaymentRequired,               'Payment Required'],
            [HttpStatusEnum::Forbidden,                     'Forbidden'],
            [HttpStatusEnum::NotFound,                      'Not Found'],
            [HttpStatusEnum::MethodNotAllowed,              'Method Not Allowed'],
            [HttpStatusEnum::NotAcceptable,                 'Not Acceptable'],
            [HttpStatusEnum::ProxyAuthenticationRequired,   'Proxy Authentication Required'],
            [HttpStatusEnum::RequestTimeout,                'Request Time-out'],
            [HttpStatusEnum::Conflict,                      'Conflict'],
            [HttpStatusEnum::Gone,                          'Gone'],
            [HttpStatusEnum::LengthRequired,                'Length Required'],
            [HttpStatusEnum::PreconditionFailed,            'Precondition Failed'],
            [HttpStatusEnum::PayloadTooLarge,               'Request Entity Too Large'],
            [HttpStatusEnum::UriTooLong,                    'Request-URI Too Large'],
            [HttpStatusEnum::UnsupportedMediaType,          'Unsupported Media Type'],
            [HttpStatusEnum::RangeNotSatisfiable,           'Requested range not satisfiable'],
            [HttpStatusEnum::ExpectationFailed,             'Expectation Failed'],
            [HttpStatusEnum::ImATeapot,                     "I'm a teapot"],
            [HttpStatusEnum::MisdirectedRequest,            'Misdirected Request'],
            [HttpStatusEnum::UnprocessableEntity,           'Unprocessable Entity'],
            [HttpStatusEnum::Locked,                        'Locked'],
            [HttpStatusEnum::FailedDependency,              'Failed Dependency'],
            [HttpStatusEnum::TooEarly,                      'Unordered Collection'],
            [HttpStatusEnum::UpgradeRequired,               'Upgrade Required'],
            [HttpStatusEnum::PreconditionRequired,          'Precondition Required'],
            [HttpStatusEnum::TooManyRequests,               'Too Many Requests'],
            [HttpStatusEnum::RequestHeaderFieldsTooLarge,   'Request Header Fields Too Large'],
            [HttpStatusEnum::UnavailableForLegalReasons,    'Unavailable For Legal Reasons'],
            [HttpStatusEnum::InternalServerError,           'Internal Server Error'],
            [HttpStatusEnum::NotImplemented,                'Not Implemented'],
            [HttpStatusEnum::BadGateway,                    'Bad Gateway'],
            [HttpStatusEnum::ServiceUnavailable,            'Service Unavailable'],
            [HttpStatusEnum::GatewayTimeout,                'Gateway Time-out'],
            [HttpStatusEnum::VersionNotSupported,           'HTTP Version not supported'],
            [HttpStatusEnum::VariantAlsoNegotiates,         'Variant Also Negotiates'],
            [HttpStatusEnum::InsufficientStorage,           'Insufficient Storage'],
            [HttpStatusEnum::LoopDetected,                  'Loop Detected'],
            [HttpStatusEnum::NotExtended,                   'Not Extended'],
            [HttpStatusEnum::NetworkAuthenticationRequired, 'Network Authentication Required'],
        ];
    }

    /**
     * Tests Phalcon\Http\Enums\HttpStatusEnum :: text()
     *
     * @dataProvider getExamples
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpEnumsHttpStatusEnumText(
        HttpStatusEnum $case,
        string $expected
    ): void {
        $this->assertSame($expected, $case->text());
    }
}
