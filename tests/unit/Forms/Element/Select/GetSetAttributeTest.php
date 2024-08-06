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

namespace Phalcon\Tests\Unit\Forms\Element\Select;

use Phalcon\Forms\Element\Select;
use Phalcon\Tests\UnitTestCase;

use function uniqid;

final class GetSetAttributeTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Forms\Element\Select :: getAttribute()/setAttribute()
     *
     * @return void
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2021-12-05
     */
    public function testFormsElementSelectGetSetAttribute(): void
    {
        $name = uniqid();
        $data = [
            'one'   => 'two',
            'three' => 'four',
        ];

        $object = new Select($name);

        $expected = 'fallback';
        $actual   = $object->getAttribute('one', 'fallback');
        $this->assertSame($expected, $actual);

        $object = new Select($name, null, $data);

        $expected = 'two';
        $actual   = $object->getAttribute('one', 'fallback');
        $this->assertSame($expected, $actual);

        $object->setAttribute('one', 'four');

        $expected = 'four';
        $actual   = $object->getAttribute('one', 'fallback');
        $this->assertSame($expected, $actual);
    }
}
