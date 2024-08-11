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

namespace Phalcon\Tests\Unit\Container\Definitions;

use Phalcon\Container\Container;
use Phalcon\Container\Definitions\AbstractDefinition;
use Phalcon\Container\Definitions\Definitions;
use Phalcon\Container\Exception\NotInstantiated;
use Phalcon\Tests\AbstractUnitTestCase;

abstract class AbstractDefinitionBase extends AbstractUnitTestCase
{
    protected Container $container;

    protected Definitions $definitions;

    public function setUp(): void
    {
        $this->definitions = new Definitions();
        $this->container   = new Container($this->definitions);
    }

    /**
     * @param AbstractDefinition $definition
     *
     * @return object
     * @throws NotInstantiated
     */
    protected function actual(AbstractDefinition $definition)
    {
        return $definition->new($this->container);
    }

    /**
     * @param AbstractDefinition $definition
     * @param array              $expected
     *
     * @return void
     */
    protected function assertNotInstantiable(
        AbstractDefinition $definition,
        array $expected
    ) {
        try {
            $this->actual($definition);
            $this->assertFalse(true, "Should not have been instantiated.");
        } catch (NotInstantiated $ex) {
            while (!empty($expected)) {
                $ex = $ex->getPrevious();
                [$expectedException, $expectedExceptionMessage] = array_shift($expected);
                $this->assertInstanceOf($expectedException, $ex);
                $this->assertSame($expectedExceptionMessage, $ex->getMessage());
            }
        }
    }
}
