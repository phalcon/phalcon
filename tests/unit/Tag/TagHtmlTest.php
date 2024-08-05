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
use Phalcon\Tests\Fixtures\Helpers\TagSetup;

class TagHtmlTest extends TagSetup
{
    /**
     * @return array[]
     */
    public static function nameProvider(): array
    {
        return [
            [
                'aside',
                Tag::XHTML10_STRICT,
                '<aside>',
            ],

            [
                'aside',
                Tag::HTML5,
                '<aside></aside>',
            ],
        ];
    }

    /**
     * Tests Phalcon\Tag :: tagHtml() - name parameter and self close
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-05
     */
    public function testTagNameSelfClose(): void
    {
        Tag::resetInput();

        $name     = 'aside';
        $expected = '<aside />';

        Tag::setDocType(Tag::XHTML10_STRICT);

        $actual = Tag::tagHtml($name, [], true);

        $this->assertSame($expected, $actual);

        Tag::resetInput();

        $name     = 'aside';
        $expected = '<aside></aside>';

        Tag::setDocType(Tag::HTML5);

        $actual = Tag::tagHtml($name, [], true);

        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Tag :: tagHtml() - name parameter
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2014-09-05
     *
     * @dataProvider nameProvider
     */
    public function testTagTagHtmlName(
        string $name,
        int $doctype,
        string $expected
    ): void {
        Tag::resetInput();

        Tag::setDocType($doctype);

        $actual = Tag::tagHtml($name);
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Tag :: tagHtml() - name parameter and EOL
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-05
     */
    public function testTagTagHtmlNameEol(): void
    {
        Tag::resetInput();

        $name     = 'aside';
        $expected = '<aside>' . PHP_EOL;

        Tag::setDocType(Tag::XHTML10_STRICT);

        $actual = Tag::tagHtml($name, [], false, false, true);

        $this->assertSame($expected, $actual);

        Tag::resetInput();

        $name     = 'aside';
        $expected = '<aside></aside>' . PHP_EOL;

        Tag::setDocType(Tag::HTML5);

        $actual = Tag::tagHtml($name, [], false, false, true);

        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Tag :: tagHtml() - name parameter and only start
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-05
     */
    public function testTagTagHtmlNameOnlyStart(): void
    {
        Tag::resetInput();

        $name     = 'aside';
        $expected = '<aside>';

        Tag::setDocType(Tag::XHTML10_STRICT);

        $actual = Tag::tagHtml($name, [], false, true);

        $this->assertSame($expected, $actual);

        Tag::resetInput();

        $name     = 'aside';
        $expected = '<aside>';

        Tag::setDocType(Tag::HTML5);

        $actual = Tag::tagHtml($name, [], false, true);

        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Tag :: tagHtml() - array parameter
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-05
     */
    public function testTagTagHtmlWithArray(): void
    {
        Tag::resetInput();

        $name    = 'canvas';
        $options = [
            'id'     => 'canvas1',
            'width'  => 300,
            'height' => 300,
        ];

        $expected = '<canvas id="canvas1" width="300" height="300">';

        Tag::setDocType(Tag::XHTML10_STRICT);

        $actual = Tag::tagHtml($name, $options);

        $this->assertSame($expected, $actual);

        Tag::resetInput();

        $name    = 'canvas';
        $options = [
            'id'     => 'canvas1',
            'width'  => 300,
            'height' => 300,
        ];

        $expected = '<canvas id="canvas1" width="300" height="300"></canvas>';

        Tag::setDocType(Tag::HTML5);

        $actual = Tag::tagHtml($name, $options);

        $this->assertSame($expected, $actual);
    }
}
