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

namespace Phalcon\Tests\Integration\Filter\Validation\Validator\Alnum;

use IntegrationTester;
use Phalcon\Filter\Validation\Validator\Alnum;
use Phalcon\Tests\Fixtures\Traits\ValidationTrait;

class HasOptionCest
{
    use ValidationTrait;

    /**
     * Tests Phalcon\Filter\Validation\Validator\Alnum :: hasOption()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function filterValidationValidatorAlnumHasOption(IntegrationTester $I)
    {
        $I->wantToTest('Validation\Validator\Alnum - hasOption()');

        $validator = new Alnum(
            [
                'message' => 'This is a message',
            ]
        );

        $this->checkHasOption($I, $validator);
    }
}
