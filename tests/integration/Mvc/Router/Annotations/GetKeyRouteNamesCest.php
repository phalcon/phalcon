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

namespace Phalcon\Tests\Integration\Mvc\Router\Annotations;

use IntegrationTester;

/**
 * Class GetSetKeyRouteNamesCest
 */
class GetKeyRouteNamesCest
{
    /**
     * Tests Phalcon\Mvc\Router\Annotations :: getKeyRouteNames()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function mvcRouterAnnotationsGetKeyRouteNames(IntegrationTester $I)
    {
        $I->wantToTest('Mvc\Router\Annotations - getKeyRouteNames()');
        $I->skipTest('Need implementation');
    }
}
