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

namespace Phalcon\Tests\Integration\Filter\Validation\Validator\Confirmation;

use IntegrationTester;
use Phalcon\Filter\Validation\Validator\Confirmation;
use Phalcon\Tests\Fixtures\Traits\ValidationTrait;

class HasOptionCest
{
    use ValidationTrait;

    /**
     * Tests Phalcon\Filter\Validation\Validator\Confirmation :: hasOption()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function filterValidationValidatorConfirmationHasOption(IntegrationTester $I)
    {
        $I->wantToTest('Validation\Validator\Confirmation - hasOption()');

        $validator = new Confirmation(
            [
                'message' => 'This is a message',
            ]
        );

        $this->checkHasOption($I, $validator);
    }
}
