<?php

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Bucket\Exception;

use Phalcon\Bucket\Exception\BucketThrowable;
use Phalcon\Bucket\Exception\Invalid;
use Phalcon\Bucket\Exception\NotFound;
use Phalcon\Tests\AbstractUnitTestCase;

final class NotFoundTest extends AbstractUnitTestCase
{
    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketExceptionNotFoundEnvNotDefined(): void
    {
        $exception = NotFound::envNotDefined('MY_VAR');
        $this->assertInstanceOf(NotFound::class, $exception);
        $this->assertSame("Environment variable 'MY_VAR' is not defined", $exception->getMessage());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketExceptionNotFoundExtendsInvalid(): void
    {
        $exception = new NotFound('test');
        $this->assertInstanceOf(Invalid::class, $exception);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketExceptionNotFoundImplementsBucketThrowable(): void
    {
        $exception = new NotFound('test');
        $this->assertInstanceOf(BucketThrowable::class, $exception);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketExceptionNotFoundInstanceNotFound(): void
    {
        $exception = NotFound::instanceNotFound('myInstance');
        $this->assertInstanceOf(NotFound::class, $exception);
        $this->assertSame("Instance 'myInstance' not found", $exception->getMessage());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketExceptionNotFoundMessage(): void
    {
        $exception = new NotFound('service not found');
        $this->assertSame('service not found', $exception->getMessage());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketExceptionNotFoundParameterNotFound(): void
    {
        $exception = NotFound::parameterNotFound('myParam');
        $this->assertInstanceOf(NotFound::class, $exception);
        $this->assertSame("Parameter 'myParam' not found", $exception->getMessage());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketExceptionNotFoundServiceNotFound(): void
    {
        $exception = NotFound::serviceNotFound('myService');
        $this->assertInstanceOf(NotFound::class, $exception);
        $this->assertSame("Service 'myService' not found", $exception->getMessage());
    }
}
