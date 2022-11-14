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

namespace Phalcon\Tests\Integration\Filter\Validation\Validator\StringLength;

use IntegrationTester;
use Phalcon\Filter\Validation\Validator\StringLength;

class SetTemplatesCest
{
    /**
     * Tests Phalcon\Filter\Validation\Validator\StringLength :: setTemplates()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-05-23
     */
    public function filterValidationValidatorStringLengthSetTemplates(IntegrationTester $I)
    {
        $I->wantToTest('Validation\Validator\StringLength - setTemplates()');

        $validator = new StringLength();

        $expected = [
            'key-1' => 'value-1',
            'key-2' => 'value-2',
            'key-3' => 'value-3',
        ];

        $actual = $validator->setTemplates($expected);

        $I->assertInstanceOf(StringLength::class, $actual, 'Instance of StringLenght');

        $I->assertEquals(
            $expected,
            $validator->getTemplates(),
            'Get equals templates'
        );
    }
}
