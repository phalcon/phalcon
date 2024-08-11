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

namespace Phalcon\Tests\Unit\Di;

use Phalcon\Di\Di;
use Phalcon\Tests\Fixtures\Di\InitializationAwareComponent;
use Phalcon\Tests\AbstractUnitTestCase;

class InitializationAwareTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Di\Di :: initialization aware interface
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-09-09
     */
    public function testDiInitializationAware(): void
    {
        $container = new Di();

        $actual = $container
            ->get(InitializationAwareComponent::class)
            ->isInitialized()
        ;
        $this->assertTrue($actual);
    }
}
