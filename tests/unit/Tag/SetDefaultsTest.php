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

namespace Phalcon\Tests\Unit\Tag;

use Phalcon\Tag;

class SetDefaultsTest extends AbstractTagSetup
{
    /**
     * Tests Phalcon\Tag :: setDefaults()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testTagSetDefaults(): void
    {
        $data = [
            'property1' => 'testVal1',
            'property2' => 'testVal2',
            'property3' => 'testVal3',
        ];

        Tag::setDefaults($data);

        $this->assertTrue(
            Tag::hasValue('property1')
        );

        $this->assertTrue(
            Tag::hasValue('property2')
        );

        $this->assertTrue(
            Tag::hasValue('property3')
        );

        $this->assertFalse(
            Tag::hasValue('property4')
        );

        $this->assertSame(
            'testVal1',
            Tag::getValue('property1')
        );

        $this->assertSame(
            'testVal2',
            Tag::getValue('property2')
        );

        $this->assertSame(
            'testVal3',
            Tag::getValue('property3')
        );
    }
}
