<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Database\DataMapper\Pdo\Profiler\Profiler;

use Phalcon\DataMapper\Pdo\Profiler\Profiler;
use Phalcon\Tests\AbstractDatabaseTestCase;

final class IsSetActiveTest extends AbstractDatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\Profiler\Profiler ::
     * isActive()/setActive()
     *
     * @since  2020-01-25
     *
     * @group mysql
     */
    public function testDmPdoProfilerProfilerIsSetActive(): void
    {
        $profiler = new Profiler();

        $this->assertFalse($profiler->isActive());

        $profiler->setActive(true);
        $this->assertTrue($profiler->isActive());
    }
}
