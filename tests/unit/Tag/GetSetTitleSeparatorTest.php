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
use Phalcon\Tests\UnitTestCase;

class GetSetTitleSeparatorTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Tag :: getTitleSeparator()/setTitleSeparator()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2012-09-05
     * @since  2018-11-13
     */
    public function testTagGetSetTitleSeparator(): void
    {
        Tag::resetInput();
        Tag::setTitleSeparator('-');

        $expected = '-';
        $actual   = Tag::getTitleSeparator();
        $this->assertSame($expected, $actual);
    }
}
