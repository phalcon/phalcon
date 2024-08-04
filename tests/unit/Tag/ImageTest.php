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

class ImageTest extends TagSetup
{
    /**
     * Tests Phalcon\Tag :: image() - string as a parameter
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-05
     */
    public function testTagImageStringParameter(): void
    {
        $options  = 'img/hello.gif';
        $expected = '<img src="/img/hello.gif"';

        $this->testFieldParameter('image', $options, $expected);
        $this->testFieldParameter('image', $options, $expected, true);
    }

    /**
     * Tests Phalcon\Tag :: image() - array as a parameter
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-05
     */
    public function testTagImageArrayParameter(): void
    {
        $options  = [
            'img/hello.gif',
            'class' => 'x_class',
        ];
        $expected = '<img src="/img/hello.gif" class="x_class"';

        $this->testFieldParameter('image', $options, $expected);
        $this->testFieldParameter('image', $options, $expected, true);
    }

    /**
     * Tests Phalcon\Tag :: image() - array as a parameters and src in it
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-05
     */
    public function testTagImageArrayParameterWithSrc(): void
    {
        $options  = [
            'img/hello.gif',
            'src'   => 'img/goodbye.gif',
            'class' => 'x_class',
        ];
        $expected = '<img src="/img/goodbye.gif" class="x_class"';

        $this->testFieldParameter('image', $options, $expected);
        $this->testFieldParameter('image', $options, $expected, true);
    }

    /**
     * Tests Phalcon\Tag :: image() - name and no src in parameter
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-05
     */
    public function testTagImageArrayParameterWithNameNoSrc(): void
    {
        $options  = [
            'img/hello.gif',
            'class' => 'x_class',
        ];
        $expected = '<img src="/img/hello.gif" class="x_class"';

        $this->testFieldParameter('image', $options, $expected);
        $this->testFieldParameter('image', $options, $expected, true);
    }

    /**
     * Tests Phalcon\Tag :: image() - setDefault
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-05
     */
    public function testTagImageWithSetDefault(): void
    {
        $options  = [
            'img/hello.gif',
            'class' => 'x_class',
        ];
        $expected = '<img src="/img/hello.gif" class="x_class"';

        $this->testFieldParameter('image', $options, $expected, false, 'setDefault');
        $this->testFieldParameter('image', $options, $expected, true, 'setDefault');
    }

    /**
     * Tests Phalcon\Tag :: image() - displayTo
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-05
     */
    public function testTagImageWithDisplayTo(): void
    {
        $options  = [
            'img/hello.gif',
            'class' => 'x_class',
        ];
        $expected = '<img src="/img/hello.gif" class="x_class"';

        $this->testFieldParameter('image', $options, $expected, false, 'displayTo');
        $this->testFieldParameter('image', $options, $expected, true, 'displayTo');
    }

    /**
     * Tests Phalcon\Tag :: image() - setDefault and element not present
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-05
     */
    public function testTagImageWithSetDefaultElementNotPresent(): void
    {
        $options  = [
            'img/hello.gif',
            'class' => 'x_class',
        ];
        $expected = '<img src="/img/hello.gif" class="x_class"';

        $this->testFieldParameter('image', $options, $expected, false, 'setDefault');
        $this->testFieldParameter('image', $options, $expected, true, 'setDefault');
    }

    /**
     * Tests Phalcon\Tag :: image() - displayTo and element not present
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-05
     */
    public function testTagImageWithDisplayToElementNotPresent(): void
    {
        $options  = [
            'img/hello.gif',
            'class' => 'x_class',
        ];
        $expected = '<img src="/img/hello.gif" class="x_class"';

        $this->testFieldParameter('image', $options, $expected, false, 'displayTo');
        $this->testFieldParameter('image', $options, $expected, true, 'displayTo');
    }

    /**
     * Tests Phalcon\Tag :: image() - string parameter and local link
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-05
     */
    public function testTagImageStringParameterLocalLink(): void
    {
        $options  = 'img/hello.gif';
        $expected = '<img src="/img/hello.gif" />';

        Tag::setDocType(Tag::XHTML10_STRICT);
        $actual = Tag::image($options, true);

        $this->assertSame($expected, $actual);

        $options  = 'img/hello.gif';
        $expected = '<img src="/img/hello.gif">';

        Tag::setDocType(Tag::HTML5);
        $actual = Tag::image($options, true);

        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Tag :: image() - string parameter and remote link
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-05
     */
    public function testTagImageStringParameterRemoteLink(): void
    {
        $options  = 'https://phalcon.io/img/hello.gif';
        $expected = '<img src="https://phalcon.io/img/hello.gif" />';

        Tag::setDocType(Tag::XHTML10_STRICT);
        $actual = Tag::image($options, false);

        $this->assertSame($expected, $actual);

        $options  = 'https://phalcon.io/img/hello.gif';
        $expected = '<img src="https://phalcon.io/img/hello.gif">';

        Tag::setDocType(Tag::HTML5);
        $actual = Tag::image($options, false);

        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Tag :: image() - array parameter and local link
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-05
     */
    public function testTagImageArrayParameterLocalLink(): void
    {
        $options  = [
            'img/hello.gif',
            'alt' => 'Hello',
        ];
        $expected = '<img src="/img/hello.gif" alt="Hello" />';

        Tag::setDocType(Tag::XHTML10_STRICT);
        $actual = Tag::image($options, true);

        $this->assertSame($expected, $actual);

        $options  = [
            'img/hello.gif',
            'alt' => 'Hello',
        ];
        $expected = '<img src="/img/hello.gif" alt="Hello">';

        Tag::setDocType(Tag::HTML5);
        $actual = Tag::image($options, true);

        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Tag :: image() - array parameter and remote link
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-05
     */
    public function testTagImageArrayParameterRemoteLink(): void
    {
        $options = [
            'https://phalcon.io/img/hello.gif',
            'alt' => 'Hello',
        ];

        Tag::setDocType(
            Tag::XHTML10_STRICT
        );

        $this->assertSame(
            '<img src="https://phalcon.io/img/hello.gif" alt="Hello" />',
            Tag::image($options, false)
        );

        $options = [
            'https://phalcon.io/img/hello.gif',
            'alt' => 'Hello',
        ];

        Tag::setDocType(
            Tag::HTML5
        );

        $this->assertSame(
            '<img src="https://phalcon.io/img/hello.gif" alt="Hello">',
            Tag::image($options, false)
        );
    }
}
