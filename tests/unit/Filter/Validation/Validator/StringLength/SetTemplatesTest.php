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

namespace Phalcon\Tests\Unit\Filter\Validation\Validator\StringLength;

use Phalcon\Filter\Validation\Validator\StringLength;
use Phalcon\Tests\AbstractUnitTestCase;

final class SetTemplatesTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Filter\Validation\Validator\StringLength :: setTemplates()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-05-23
     */
    public function testFilterValidationValidatorStringLengthSetTemplates(): void
    {
        $validator = new StringLength();

        $expected = [
            'key-1' => 'value-1',
            'key-2' => 'value-2',
            'key-3' => 'value-3',
        ];

        $actual = $validator->setTemplates($expected);

        $this->assertInstanceOf(StringLength::class, $actual, 'Instance of StringLenght');

        $this->assertSame(
            $expected,
            $validator->getTemplates(),
            'Get equals templates'
        );
    }
}
