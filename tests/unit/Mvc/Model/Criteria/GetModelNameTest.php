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

namespace Phalcon\Tests\Unit\Mvc\Model\Criteria;

use Phalcon\Tests\DatabaseTestCase;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Tests\Models\Invoices;

final class GetModelNameTest extends DatabaseTestCase
{
    /**
     * Tests Phalcon\Mvc\Model\Criteria :: getModelName()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-02-01
     *
     * @group  common
     */
    public function testMvcModelCriteriaGetModelName(): void
    {
        $criteria = new Criteria();

        $criteria->setModelName(Invoices::class);

        $expected = Invoices::class;
        $actual   = $criteria->getModelName();
        $this->assertEquals($expected, $actual);
    }
}
