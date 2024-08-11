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

namespace Phalcon\Tests\Unit\Filter\Validation\Validator;

use Phalcon\Tests\Fixtures\Traits\ValidationTrait;
use Phalcon\Tests\AbstractUnitTestCase;

use function uniqid;

final class GetSetOptionTest extends AbstractUnitTestCase
{
    use ValidationTrait;

    /**
     * Tests Phalcon\Filter\Validation\Validator\Url :: getOption()/setOption()
     *
     * @dataProvider getClasses
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2018-11-13
     */
    public function testFilterValidationValidatorUrlGetSetOption(
        string $class
    ): void {
        $validator = new $class();

        $this->assertFalse($validator->hasOption('option'));

        $source = uniqid('val-');
        $validator->setOption('option', $source);

        $this->assertTrue($validator->hasOption('option'));

        $expected = $source;
        $actual   = $validator->getOption('option');
        $this->assertEquals($expected, $actual);
    }
}
