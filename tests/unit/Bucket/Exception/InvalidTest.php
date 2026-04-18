<?php

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Bucket\Exception;

use Exception;
use Phalcon\Bucket\Exception\BucketThrowable;
use Phalcon\Bucket\Exception\Invalid;
use Phalcon\Tests\AbstractUnitTestCase;
use Throwable;

final class InvalidTest extends AbstractUnitTestCase
{
    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketExceptionInvalidCannotExtendResolved(): void
    {
        $exception = Invalid::cannotExtendResolved('myService');
        $this->assertInstanceOf(Invalid::class, $exception);
        $this->assertSame("Cannot extend already-resolved service 'myService'", $exception->getMessage());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketExceptionInvalidCannotResolveParameter(): void
    {
        $exception = Invalid::cannotResolveParameter('param', 'MyClass');
        $this->assertInstanceOf(Invalid::class, $exception);
        $this->assertSame("Cannot resolve parameter '\$param' for 'MyClass'", $exception->getMessage());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketExceptionInvalidCircularAlias(): void
    {
        $exception = Invalid::circularAlias('myAlias');
        $this->assertInstanceOf(Invalid::class, $exception);
        $this->assertSame("Circular alias detected: 'myAlias'", $exception->getMessage());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketExceptionInvalidExtendsException(): void
    {
        $exception = new Invalid('test');
        $this->assertInstanceOf(Exception::class, $exception);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketExceptionInvalidFrozenDefinition(): void
    {
        $exception = Invalid::frozenDefinition('myService');
        $this->assertInstanceOf(Invalid::class, $exception);
        $this->assertSame("Cannot modify frozen definition 'myService'", $exception->getMessage());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketExceptionInvalidImplementsBucketThrowable(): void
    {
        $exception = new Invalid('test');
        $this->assertInstanceOf(BucketThrowable::class, $exception);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketExceptionInvalidImplementsThrowable(): void
    {
        $exception = new Invalid('test');
        $this->assertInstanceOf(Throwable::class, $exception);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketExceptionInvalidMessage(): void
    {
        $exception = new Invalid('something went wrong');
        $this->assertSame('something went wrong', $exception->getMessage());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketExceptionInvalidNoClassSet(): void
    {
        $exception = Invalid::noClassSet('myService');
        $this->assertInstanceOf(Invalid::class, $exception);
        $this->assertSame("No class set for service 'myService'", $exception->getMessage());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketExceptionInvalidNoFactorySet(): void
    {
        $exception = Invalid::noFactorySet('myService');
        $this->assertInstanceOf(Invalid::class, $exception);
        $this->assertSame("No factory set for service 'myService'", $exception->getMessage());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketExceptionInvalidNoProcessorFound(): void
    {
        $exception = Invalid::noProcessorFound();
        $this->assertInstanceOf(Invalid::class, $exception);
        $this->assertSame('No processor found for the given definition', $exception->getMessage());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketExceptionInvalidServiceNotFound(): void
    {
        $exception = Invalid::serviceNotFound('myService');
        $this->assertInstanceOf(Invalid::class, $exception);
        $this->assertSame("Service 'myService' not registered", $exception->getMessage());
    }
}
