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

namespace Phalcon\Tests\Unit\Mvc\Model\Row;

use Phalcon\Tests\DatabaseTestCase;
use Phalcon\Mvc\Model\Exception;
use Phalcon\Mvc\Model\Row;

final class OffsetUnsetTest extends DatabaseTestCase
{
    /**
     * Tests Phalcon\Mvc\Model\Row :: offsetUnset()
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-06-01
     *
     * @group  common
     */
    public function testMvcModelRowOffsetUnset(): void
    {
        $row = new Row();

        $row->writeAttribute('inv_id', 1);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Row is an immutable ArrayAccess object');

        unset($row['inv_id']);
    }
}
