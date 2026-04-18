<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Implementation of this file has been heavily influenced by CapsulePHP.
 * Additionally, there are implementations from ioc-interop, which is a
 * Composer dependency, and from service-interop and resolver-interop. The
 * latter two are copied and re-implemented here: service-interop is not yet
 * published on Packagist, and resolver-interop requires PHP 8.4 (this project
 * targets PHP 8.1). Once both packages become available and compatible, the
 * copies will be replaced with the actual Composer dependencies.
 *
 * @link    https://github.com/capsulephp/di
 * @license https://github.com/capsulephp/di/blob/3.x/LICENSE.md
 *
 * @link    https://github.com/ioc-interop/interface
 * @license https://github.com/ioc-interop/interface/blob/1.x/LICENSE.md
 *
 * @link    https://github.com/service-interop/interface
 * @license https://github.com/service-interop/interface/blob/1.x/LICENSE.md
 *
 * @link    https://github.com/resolver-interop/interface/tree/1.x
 * @license https://github.com/resolver-interop/interface/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Bucket\Definition\Processor;

use Phalcon\Bucket\Definition\DefinitionType;
use Phalcon\Bucket\Definition\Processor\ObjectProcessor;
use Phalcon\Tests\AbstractUnitTestCase;
use Phalcon\Tests\Unit\Bucket\Fake\FakeContainer;
use stdClass;

final class ObjectProcessorTest extends AbstractUnitTestCase
{
    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketDefinitionProcessorObjectProcessorCanProcessReturnsFalseForClosure(): void
    {
        $processor = new ObjectProcessor();
        $closure   = static function () {
            return new stdClass();
        };

        $this->assertFalse($processor->canProcess($closure));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketDefinitionProcessorObjectProcessorCanProcessReturnsFalseForNonObject(): void
    {
        $processor = new ObjectProcessor();

        $this->assertFalse($processor->canProcess('some string'));
        $this->assertFalse($processor->canProcess(42));
        $this->assertFalse($processor->canProcess(null));
        $this->assertFalse($processor->canProcess([]));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketDefinitionProcessorObjectProcessorCanProcessReturnsTrueForObject(): void
    {
        $processor = new ObjectProcessor();

        $this->assertTrue($processor->canProcess(new stdClass()));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketDefinitionProcessorObjectProcessorProcessReturnsCorrectType(): void
    {
        $processor = new ObjectProcessor();
        $instance  = new stdClass();

        $def = $processor->process('myService', $instance, new FakeContainer());

        $this->assertSame(DefinitionType::OBJECT, $def->getType());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketDefinitionProcessorObjectProcessorProcessSetsFactory(): void
    {
        $processor = new ObjectProcessor();
        $instance  = new stdClass();
        $container = new FakeContainer();

        $def = $processor->process('myService', $instance, $container);

        $this->assertTrue($def->hasFactory());
        $factory = $def->getFactory();
        $result  = $factory($container);
        $this->assertSame($instance, $result);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketDefinitionProcessorObjectProcessorProcessSetsServiceName(): void
    {
        $processor = new ObjectProcessor();
        $instance  = new stdClass();

        $def = $processor->process('myService', $instance, new FakeContainer());

        $this->assertSame('myService', $def->getServiceName());
    }
}
