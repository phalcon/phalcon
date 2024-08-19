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

class SetDefaultTest extends AbstractTagSetup
{
    /**
     * Tests Phalcon\Tag :: setDefault()
     *
     * @issue  https://github.com/phalcon/cphalcon/issues/2402
     * @author Dmitry Patsura <talk@dmtry.me>
     * @since  2014-05-10
     */
    public function testTagSetDefault(): void
    {
        Tag::setDefault('property1', 'testVal1');
        Tag::setDefault('property2', 'testVal2');
        Tag::setDefault('property3', 'testVal3');

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
