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

namespace Phalcon\Tests\Unit\Filter\Validation\Validator\StringLength\Min;

use Phalcon\Filter\Validation\Validator\StringLength\Min;
use Phalcon\Tests\UnitTestCase;

final class GetOptionTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Filter\Validation\Validator\StringLength\Min :: getOption()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-05-23
     */
    public function testFilterValidationValidatorStringLengthMinGetOption(): void
    {
        $validator = new Min();

        $this->assertSame(null, $validator->getOption('min'), 'Min option is null by default');

        $expected = 1234;
        $validator->setOption('min', $expected);
        $this->assertSame($expected, $validator->getOption('min'), 'Min option is 1234');

        $expected = '1234';
        $validator->setOption('min', $expected);
        $this->assertSame($expected, $validator->getOption('min'), 'Min option is "1234"');
    }
}
