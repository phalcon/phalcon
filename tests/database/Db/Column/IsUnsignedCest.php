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

namespace Phalcon\Tests\Database\Db\Column;

use DatabaseTester;
use Phalcon\Tests\Fixtures\Traits\DbTrait;

class IsUnsignedCest
{
    use DbTrait;

    /**
     * Tests Phalcon\Db\Column :: isUnsigned()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-02-01
     *
     * @group  common
     */
    public function dbColumnIsUnsigned(DatabaseTester $I)
    {
        $I->wantToTest("Db\Column - isUnsigned()");

        $columns         = $this->getColumnsArray();
        $expectedColumns = $this->getColumnsObjects();

        foreach ($expectedColumns as $index => $column) {
            $I->assertSame(
                $columns[$index]['unsigned'],
                $column->isUnsigned()
            );
        }
    }
}
