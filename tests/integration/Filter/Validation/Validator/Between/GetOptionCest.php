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

namespace Phalcon\Tests\Integration\Filter\Validation\Validator\Between;

use IntegrationTester;
use Phalcon\Filter\Validation\Validator\Between;
use Phalcon\Tests\Fixtures\Traits\ValidationTrait;

/**
 * Class GetOptionCest
 */
class GetOptionCest
{
    use ValidationTrait;

    /**
     * Tests Phalcon\Filter\Validation\Validator\Between :: getOption()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function filterValidationValidatorBetweenGetOption(IntegrationTester $I)
    {
        $I->wantToTest('Validation\Validator\Between - getOption()');

        $validator = new Between();

        $this->checkGetOption($I, $validator);
    }
}
