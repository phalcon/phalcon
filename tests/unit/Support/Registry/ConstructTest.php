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

namespace Phalcon\Tests\Unit\Support\Registry;

use Phalcon\Support\Registry;
use Phalcon\Tests\UnitTestCase;

final class ConstructTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Support\Registry :: __construct()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testSupportRegistryConstruct(): void
    {
        $registry = new Registry();

        $this->assertInstanceOf(
            Registry::class,
            $registry
        );
    }
}
