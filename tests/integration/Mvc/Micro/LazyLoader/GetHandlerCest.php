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

namespace Phalcon\Tests\Integration\Mvc\Micro\LazyLoader;

use IntegrationTester;

/**
 * Class GetHandlerCest
 */
class GetHandlerCest
{
    /**
     * Tests Phalcon\Mvc\Micro\LazyLoader :: getHandler()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-19
     */
    public function mvcMicroLazyloaderGetHandler(IntegrationTester $I)
    {
        $I->wantToTest('Assets\Manager - getJs()');
        $I->skipTest('Need implementation');
    }
}
