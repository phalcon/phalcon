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
use Phalcon\Mvc\Model\Row;

final class OffsetGetTest extends DatabaseTestCase
{
    /**
     * Tests Phalcon\Mvc\Model\Row :: offsetGet()
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-06-01
     *
     * @group  common
     */
    public function testMvcModelRowOffsetGet(): void
    {
        $row = new Row();

        $row->writeAttribute('inv_title', 'Sample title');

        $this->assertEquals(
            'Sample title',
            $row['inv_title']
        );
    }
}
