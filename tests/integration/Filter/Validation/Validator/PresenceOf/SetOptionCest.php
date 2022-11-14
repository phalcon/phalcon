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

namespace Phalcon\Tests\Integration\Filter\Validation\Validator\PresenceOf;

use IntegrationTester;
use Phalcon\Filter\Validation\Validator\PresenceOf;
use Phalcon\Tests\Fixtures\Traits\ValidationTrait;

/**
 * Class SetOptionCest
 */
class SetOptionCest
{
    use ValidationTrait;

    /**
     * Tests Phalcon\Filter\Validation\Validator\PresenceOf :: setOption()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function filterValidationValidatorPresenceOfSetOption(IntegrationTester $I)
    {
        $I->wantToTest('Validation\Validator\PresenceOf - setOption()');

        $validator = new PresenceOf();

        $this->checkSetOption($I, $validator);
    }
}
