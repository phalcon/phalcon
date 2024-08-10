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

class AbstractTagHtmlCloseTest extends AbstractTagSetup
{
    /**
     * Tests Phalcon\Tag :: tagHtmlClose()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-05
     */
    public function testTagTagHtmlClose(): void
    {
        Tag::resetInput();

        $name     = 'canvas';
        $expected = '</canvas>';

        Tag::setDocType(
            Tag::XHTML10_STRICT
        );

        $actual = Tag::tagHtmlClose($name);

        $this->assertSame($expected, $actual);

        Tag::resetInput();

        $name     = 'canvas';
        $expected = '</canvas>';

        Tag::setDocType(
            Tag::HTML5
        );

        $actual = Tag::tagHtmlClose($name);

        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Tag :: tagHtmlClose() - EOL
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-05
     */
    public function testTagTagHtmlCloseEol(): void
    {
        Tag::resetInput();

        $name     = 'canvas';
        $expected = '</canvas>' . PHP_EOL;

        Tag::setDocType(
            Tag::XHTML10_STRICT
        );

        $actual = Tag::tagHtmlClose($name, true);

        $this->assertSame($expected, $actual);

        Tag::resetInput();

        $name     = 'canvas';
        $expected = '</canvas>' . PHP_EOL;

        Tag::setDocType(
            Tag::HTML5
        );

        $actual = Tag::tagHtmlClose($name, true);

        $this->assertSame($expected, $actual);
    }
}
