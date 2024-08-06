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

namespace Phalcon\Tests\Database\Mvc\Model\Criteria;

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Tests\DatabaseTestCase;

final class DistinctTest extends DatabaseTestCase
{
    /**
     * Tests Phalcon\Mvc\Model\Criteria :: distinct()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-02-01
     *
     * @group  common
     */
    public function testMvcModelCriteriaDistinct(): void
    {
        $criteria = new Criteria();

        $expected = [];
        $actual   = $criteria->getParams();
        $this->assertEquals($expected, $actual);

        $criteria->distinct('inv_cst_id');

        $expected = [
            'distinct' => 'inv_cst_id',
        ];
        $actual   = $criteria->getParams();
        $this->assertEquals($expected, $actual);
    }
}
