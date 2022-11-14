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

namespace Phalcon\Tests\Integration\Filter\Validation\Validator\Identical;

use IntegrationTester;
use Phalcon\Filter\Validation\Validator\Identical;
use Phalcon\Tests\Fixtures\Traits\ValidationTrait;

/**
 * Class GetOptionCest
 */
class GetOptionCest
{
    use ValidationTrait;

    /**
     * Tests Phalcon\Filter\Validation\Validator\Identical :: getOption()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function filterValidationValidatorIdenticalGetOption(IntegrationTester $I)
    {
        $I->wantToTest('Validation\Validator\Identical - getOption()');

        $validator = new Identical();

        $this->checkGetOption($I, $validator);
    }
}
