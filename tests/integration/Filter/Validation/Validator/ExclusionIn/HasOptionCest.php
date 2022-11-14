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

namespace Phalcon\Tests\Integration\Filter\Validation\Validator\ExclusionIn;

use IntegrationTester;
use Phalcon\Filter\Validation\Validator\ExclusionIn;
use Phalcon\Tests\Fixtures\Traits\ValidationTrait;

/**
 * Class HasOptionCest
 */
class HasOptionCest
{
    use ValidationTrait;

    /**
     * Tests Phalcon\Filter\Validation\Validator\ExclusionIn :: hasOption()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function filterValidationValidatorExclusionInHasOption(IntegrationTester $I)
    {
        $I->wantToTest('Validation\Validator\ExclusionIn - hasOption()');

        $validator = new ExclusionIn(
            [
                'message' => 'This is a message',
            ]
        );

        $this->checkHasOption($I, $validator);
    }
}
