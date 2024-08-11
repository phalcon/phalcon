<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Database\Db\Column;

use Phalcon\Tests\AbstractDatabaseTestCase;
use Phalcon\Tests\Fixtures\Traits\DbTrait;

final class GetSizeTest extends AbstractDatabaseTestCase
{
    use DbTrait;

    /**
     * Tests Phalcon\Db\Column :: getSize()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-02-01
     *
     * @group  common
     */
    public function testDbColumnGetSize(): void
    {
        $columns         = $this->getColumnsArray();
        $expectedColumns = $this->getColumnsObjects();

        foreach ($expectedColumns as $index => $column) {
            $this->assertSame(
                $columns[$index]['size'],
                $column->getSize()
            );
        }
    }
}
