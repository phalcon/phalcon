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
 * Class GetOptionCest
 */
class GetOptionCest
{
    use ValidationTrait;

    /**
     * Tests Phalcon\Filter\Validation\Validator\ExclusionIn :: getOption()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function filterValidationValidatorExclusionInGetOption(IntegrationTester $I)
    {
        $I->wantToTest('Validation\Validator\ExclusionIn - getOption()');

        $validator = new ExclusionIn();

        $this->checkGetOption($I, $validator);
    }
}
