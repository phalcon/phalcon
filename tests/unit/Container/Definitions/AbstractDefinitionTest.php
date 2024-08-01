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
use UnitTester;

abstract class AbstractDefinitionTest
{
    protected Container $container;

    protected Definitions $definitions;

    public function _before(): void
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
     * @param UnitTester         $I
     * @param AbstractDefinition $definition
     * @param array              $expected
     *
     * @return void
     */
    protected function assertNotInstantiable(
        UnitTester $I,
        AbstractDefinition $definition,
        array $expected
    ) {
        try {
            $this->actual($definition);
            $I->assertFalse(true, "Should not have been instantiated.");
        } catch (NotInstantiated $ex) {
            while (!empty($expected)) {
                $ex = $ex->getPrevious();
                [$expectedException, $expectedExceptionMessage] = array_shift($expected);
                $I->assertInstanceOf($expectedException, $ex);
                $I->assertSame($expectedExceptionMessage, $ex->getMessage());
            }
        }
    }
}
