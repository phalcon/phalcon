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

use Phalcon\Tests\UnitTestCase;
use Phalcon\Filter\Validation\Validator\StringLength;

final class SetTemplateTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Filter\Validation\Validator\StringLength :: setTemplate()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-05-23
     */
    public function testFilterValidationValidatorStringLengthSetTemplate(): void
    {
        $validator = new StringLength();

        $expected = 'New custom template message';

        $actual = $validator->setTemplate($expected);

        $this->assertInstanceOf(StringLength::class, $actual, 'Instance of StringLenght');

        $this->assertSame(
            $expected,
            $validator->getTemplate(),
            'Get equals template message'
        );
    }
}
