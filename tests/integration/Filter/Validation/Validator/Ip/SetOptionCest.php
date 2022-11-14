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

namespace Phalcon\Tests\Integration\Filter\Validation\Validator\Ip;

use IntegrationTester;
use Phalcon\Filter\Validation\Validator\Ip;
use Phalcon\Tests\Fixtures\Traits\ValidationTrait;

/**
 * Class SetOptionCest
 */
class SetOptionCest
{
    use ValidationTrait;

    /**
     * Tests Phalcon\Filter\Validation\Validator\Ip :: setOption()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function filterValidationValidatorIpSetOption(IntegrationTester $I)
    {
        $I->wantToTest('Validation\Validator\Ip - setOption()');

        $validator = new Ip();

        $this->checkSetOption($I, $validator);
    }
}
