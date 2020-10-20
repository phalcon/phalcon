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

namespace Phalcon\Tests\Unit\Di\Service\Builder;

use UnitTester;

/**
 * Class BuildCest
 *
 * @package Phalcon\Tests\Unit\Di\Service\Builder
 */
class BuildCest
{
    /**
     * Unit Tests Phalcon\Di\Service\Builder :: build()
     *
     * @param  UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-09-09
     */
    public function diServiceBuilderBuild(UnitTester $I)
    {
        $I->wantToTest('Di\Service\Builder - build()');

        $I->skipTest('Need implementation');
    }
}
