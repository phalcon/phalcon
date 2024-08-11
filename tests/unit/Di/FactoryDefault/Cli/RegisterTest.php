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

namespace Phalcon\Tests\Unit\Di\FactoryDefault\Cli;

use Phalcon\Di\FactoryDefault\Cli as Di;
use Phalcon\Tests\Fixtures\Di\SomeComponent;
use Phalcon\Tests\Fixtures\Di\SomeServiceProvider;
use Phalcon\Tests\AbstractUnitTestCase;

final class RegisterTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Di\FactoryDefault\Cli :: register()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testDiFactorydefaultCliRegister(): void
    {
        require_once dataDir('fixtures/Di/SomeComponent.php');
        require_once dataDir('fixtures/Di/SomeServiceProvider.php');

        $di = new Di();

        $di->register(new SomeServiceProvider());

        $this->assertEquals('bar', $di->get('foo'));
        $this->assertInstanceOf(SomeComponent::class, $di->get('fooAction'));
    }
}
