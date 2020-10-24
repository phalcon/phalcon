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

namespace Phalcon\Test\Unit\Config\Adapter\Grouped;

use Phalcon\Tests\Fixtures\Traits\ConfigTrait;
use UnitTester;

class OffsetSetCest
{
    use ConfigTrait;

    /**
     * Tests Phalcon\Config\Adapter\Grouped :: offsetSet()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function configAdapterGroupedOffsetSet(UnitTester $I)
    {
        $this->checkOffsetSet($I, 'Grouped');
    }
}
