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

namespace Phalcon\Tests\Integration\Filter\Validation;

use IntegrationTester;

/**
 * Class SetDefaultMessagesCest
 */
class SetDefaultMessagesCest
{
    /**
     * Tests Phalcon\Filter\Validation :: setDefaultMessages()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-04-16
     */
    public function filterValidationSetDefaultMessages(IntegrationTester $I)
    {
        $I->wantToTest('Validation - setDefaultMessages()');

        $I->skipTest('Need implementation');
    }
}
