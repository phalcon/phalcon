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

class IsPrimaryCest
{
    use DbTrait;

    /**
     * Tests Phalcon\Db\Column :: isPrimary()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-02-01
     *
     * @group  common
     */
    public function dbColumnIsPrimary(DatabaseTester $I)
    {
        $I->wantToTest("Db\Column - isPrimary()");

        $columns         = $this->getColumnsArray();
        $expectedColumns = $this->getColumnsObjects();

        foreach ($expectedColumns as $index => $column) {
            $I->assertSame(
                $columns[$index]['primary'],
                $column->isPrimary()
            );
        }
    }
}
