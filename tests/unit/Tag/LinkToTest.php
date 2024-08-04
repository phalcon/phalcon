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

class LinkToTest extends TagSetup
{
    /**
     * Tests Phalcon\Tag :: linkTo() - string as URL and name
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-29
     */
    public function testTagLinkToWithStringAsURLAndName(): void
    {
        $url  = 'x_url';
        $name = 'x_name';

        $this->assertSame(
            '<a href="/x_url">x_name</a>',
            Tag::linkTo($url, $name)
        );
    }

    /**
     * Tests Phalcon\Tag :: linkTo() - string as URL and name
     *
     * @issue  https://github.com/phalcon/cphalcon/issues/2002
     * @author Phalcon Team <team@phalcon.io>
     * @author Dreamszhu <dreamsxin@qq.com>
     * @since  2014-03-10
     */
    public function testTagLinkToWithQueryParam(): void
    {
        $actual = Tag::linkTo(
            [
                'signup/register',
                'Register Here!',
                'class' => 'btn-primary',
                'query' => [
                    'from'  => 'github',
                    'token' => '123456',
                ],
            ]
        );

        $this->assertSame(
            '<a href="/signup/register?from=github&amp;token=123456" class="btn-primary">Register Here!</a>',
            $actual
        );
    }

    /**
     * Tests Phalcon\Tag :: linkTo() - empty string as URL and string as name
     * parameter
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-29
     */
    public function testTagLinkToWithEmptyStringAsURLAndStringAsName(): void
    {
        $url  = '';
        $name = 'x_name';

        $this->assertSame(
            '<a href="/">x_name</a>',
            Tag::linkTo($url, $name)
        );
    }

    /**
     * Tests Phalcon\Tag :: linkTo() - array as a parameter
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-29
     */
    public function testTagLinkToArrayParameter(): void
    {
        $options = [
            'x_url',
            'x_name',
        ];

        $this->assertSame(
            '<a href="/x_url">x_name</a>',
            Tag::linkTo($options)
        );
    }

    /**
     * Tests Phalcon\Tag :: linkTo() - named array as a parameter
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-29
     */
    public function testTagLinkToNamedArrayParameter(): void
    {
        $options = [
            'action' => 'x_url',
            'text'   => 'x_name',
            'class'  => 'x_class',
        ];

        $this->assertSame(
            '<a href="/x_url" class="x_class">x_name</a>',
            Tag::linkTo($options)
        );
    }

    /**
     * Tests Phalcon\Tag :: linkTo() - complex local URL
     *
     * @issue  https://github.com/phalcon/cphalcon/issues/1679
     * @author Phalcon Team <team@phalcon.io>
     * @author Dreamszhu <dreamsxin@qq.com>
     * @since  2014-09-29
     */
    public function testTagLinkToWithComplexLocalUrl(): void
    {
        Tag::resetInput();

        $url  = 'x_action/x_param';
        $name = 'x_name';

        $this->assertSame(
            '<a href="/x_action/x_param">x_name</a>',
            Tag::linkTo($url, $name)
        );

        Tag::resetInput();

        $options = [
            'x_action/x_param',
            'x_name',
        ];

        $this->assertSame(
            '<a href="/x_action/x_param">x_name</a>',
            Tag::linkTo($options)
        );

        Tag::resetInput();

        $options = [
            'x_action/x_param',
            'x_name',
            'class' => 'x_class',
        ];

        $this->assertSame(
            '<a href="/x_action/x_param" class="x_class">x_name</a>',
            Tag::linkTo($options)
        );
    }

    /**
     * Tests Phalcon\Tag :: linkTo() - complex remote URL
     *
     * @issue  https://github.com/phalcon/cphalcon/issues/1679
     * @author Phalcon Team <team@phalcon.io>
     * @author Dreamszhu <dreamsxin@qq.com>
     * @since  2014-09-29
     */
    public function testTagLinkToWithComplexRemoteUrl(): void
    {
        Tag::resetInput();

        $url  = 'http://phalcon.io/en/';
        $name = 'x_name';

        $this->assertSame(
            '<a href="http://phalcon.io/en/">x_name</a>',
            Tag::linkTo($url, $name, false)
        );

        Tag::resetInput();

        $options = [
            'http://phalcon.io/en/',
            'x_name',
            false,
        ];

        $this->assertSame(
            '<a href="http://phalcon.io/en/">x_name</a>',
            Tag::linkTo($options)
        );

        Tag::resetInput();

        $options = [
            'http://phalcon.io/en/',
            'text'  => 'x_name',
            'local' => false,
        ];

        $this->assertSame(
            '<a href="http://phalcon.io/en/">x_name</a>',
            Tag::linkTo($options)
        );

        Tag::resetInput();

        $url  = 'mailto:someone@phalcon.io';
        $name = 'someone@phalcon.io';

        $this->assertSame(
            '<a href="mailto:someone@phalcon.io">someone@phalcon.io</a>',
            Tag::linkTo($url, $name, false)
        );
    }
}
