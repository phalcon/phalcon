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

namespace Phalcon\Tests\Integration\Filter\Validation\Validator\Date;

use IntegrationTester;
use Phalcon\Filter\Validation\Validator\Date;
use Phalcon\Tests\Fixtures\Traits\ValidationTrait;

/**
 * Class HasOptionCest
 */
class HasOptionCest
{
    use ValidationTrait;

    /**
     * Tests Phalcon\Filter\Validation\Validator\Date :: hasOption()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function filterValidationValidatorDateHasOption(IntegrationTester $I)
    {
        $I->wantToTest('Validation\Validator\Date - hasOption()');

        $validator = new Date(
            [
                'message' => 'This is a message',
            ]
        );

        $this->checkHasOption($I, $validator);
    }
}
