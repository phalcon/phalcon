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

namespace Phalcon\Tests\Integration\Filter\Validation\Validator\StringLength\Min;

use IntegrationTester;
use Phalcon\Filter\Validation\Validator\StringLength\Min;
use Phalcon\Tests\Fixtures\Traits\ValidationTrait;

class ConstructCest
{
    use ValidationTrait;

    /**
     * Tests Phalcon\Filter\Validation\Validator\StringLength\Min :: __construct()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-05-23
     */
    public function filterValidationValidatorStringLengthMinConstruct(IntegrationTester $I)
    {
        $I->wantToTest('Validation\Validator\StringLength\Min - __construct()');

        $validator = new Min();

        $this->checkConstruct($I, $validator);
    }
}
