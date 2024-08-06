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

namespace Phalcon\Tests\Database\Mvc\Model\Row;

use Phalcon\Mvc\Model\Row;
use Phalcon\Tests\DatabaseTestCase;

final class SetDirtyStateTest extends DatabaseTestCase
{
    /**
     * Tests Phalcon\Mvc\Model\Row :: setDirtyState()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-02-01
     *
     * @group  common
     */
    public function testMvcModelRowSetDirtyState(): void
    {
        $row = new Row();

        $this->assertFalse($row->setDirtyState(0));
        $this->assertFalse($row->setDirtyState(1));
    }
}
