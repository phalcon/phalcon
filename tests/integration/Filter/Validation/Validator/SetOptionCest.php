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

namespace Phalcon\Tests\Integration\Filter\Validation\Validator;

use IntegrationTester;

/**
 * Class SetOptionCest
 */
class SetOptionCest
{
    /**
     * Tests Phalcon\Filter\Validation\Validator :: setOption()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function filterValidationValidatorSetOption(IntegrationTester $I)
    {
        $I->wantToTest('Validation\Validator - setOption()');
        $I->skipTest('Need implementation');
    }
}
