<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\DataMapper\Pdo\Profiler\Profiler;

use Phalcon\Tests\DatabaseTestCase;
use Phalcon\DataMapper\Pdo\Profiler\Profiler;

final class ConstructTest extends DatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\Profiler\Profiler :: __construct()
     *
     * @since  2020-01-25
     *
     * @group  common
     */
    public function testDmPdoProfilerProfilerConstruct(): void
    {
        $profiler = new Profiler();

        $this->assertInstanceOf(Profiler::class, $profiler);
    }
}
