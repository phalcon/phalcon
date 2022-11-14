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

namespace Phalcon\Tests\Integration\Filter\Validation\Validator\Uniqueness;

use IntegrationTester;
use Phalcon\Filter\Validation\Validator\Uniqueness;
use Phalcon\Tests\Fixtures\Traits\ValidationTrait;

/**
 * Class SetOptionCest
 */
class SetOptionCest
{
    use ValidationTrait;

    /**
     * Tests Phalcon\Filter\Validation\Validator\Uniqueness :: setOption()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function filterValidationValidatorUniquenessSetOption(IntegrationTester $I)
    {
        $I->wantToTest('Validation\Validator\Uniqueness - setOption()');

        $validator = new Uniqueness();

        $this->checkSetOption($I, $validator);
    }
}
