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
use Phalcon\Tests\Fixtures\Helpers\AbstractTagSetup;

class DisplayToTest extends AbstractTagSetup
{
    /**
     * Tests Phalcon\Tag :: displayTo()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testTagDisplayTo(): void
    {
        //Init Data
        Tag::displayTo('city', 'Miramas');
        Tag::displayTo('country', 'France');
        Tag::displayTo('zipcode', '13140');

        //check if exists
        $this->assertTrue(
            Tag::hasValue('city')
        );
        $this->assertTrue(
            Tag::hasValue('country')
        );
        $this->assertTrue(
            Tag::hasValue('zipcode')
        );
        $this->assertFalse(
            Tag::hasValue('area')
        );

        //Check value
        $this->assertSame(
            'Miramas',
            Tag::getValue('city')
        );
        $this->assertSame(
            'France',
            Tag::getValue('country')
        );
        $this->assertSame(
            '13140',
            Tag::getValue('zipcode')
        );
    }
}
