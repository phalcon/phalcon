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

class FriendlyTitleTest extends AbstractTagSetup
{
    /**
     * Tests Phalcon\Tag :: friendlyTitle() - string as a parameter lowercase
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-11
     */
    public function testFriendlyTitleStringParameterLowercase(): void
    {
        $options  = 'This is a Test';
        $expected = 'this_is_a_test';
        $actual   = Tag::friendlyTitle($options, '_', true);

        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Tag :: friendlyTitle() - string as a parameter and no
     * separator
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-11
     */
    public function testFriendlyTitleStringParameterNoSeparator(): void
    {
        $options  = 'This is a Test';
        $expected = 'this-is-a-test';
        $actual   = Tag::friendlyTitle($options);

        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Tag :: friendlyTitle() - string as a parameter with
     * replace as array
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-11
     */
    public function testFriendlyTitleStringParameterReplaceArray(): void
    {
        $options  = 'This is a Test';
        $expected = 't_s_s_a_test';
        $actual   = Tag::friendlyTitle(
            $options,
            '_',
            true,
            ['i', 'h']
        );

        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Tag :: friendlyTitle() - string as a parameter with
     * replace as string
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-11
     */
    public function testFriendlyTitleStringParameterReplaceString(): void
    {
        $options  = 'This is a Test';
        $expected = 'th_s_s_a_test';
        $actual   = Tag::friendlyTitle($options, '_', true, 'i');

        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Tag :: friendlyTitle() - string as a parameter and a
     * separator
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-11
     */
    public function testFriendlyTitleStringParameterSeparator(): void
    {
        $options  = 'This is a Test';
        $expected = 'this_is_a_test';
        $actual   = Tag::friendlyTitle($options, '_');

        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Tag :: friendlyTitle() - string as a parameter uppercase
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-11
     */
    public function testFriendlyTitleStringParameterUppercase(): void
    {
        $options  = 'This is a Test';
        $expected = 'This_is_a_Test';
        $actual   = Tag::friendlyTitle($options, '_', false);

        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Tag :: friendlyTitle() - accented characters and replace
     * array
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-11
     */
    public function testFriendlyTitleWithAccentedCharactersAndReplaceArray(): void
    {
        $options = "Perché l'erba è verde?";

        $this->assertSame(
            'P_rch_l_rb_v_rd',
            Tag::friendlyTitle(
                $options,
                '_',
                false,
                ['e', 'a']
            )
        );
    }

    /**
     * Tests Phalcon\Tag :: friendlyTitle() - accented characters and replace
     * string
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-11
     */
    public function testFriendlyTitleWithAccentedCharactersAndReplaceString(): void
    {
        $options = "Perché l'erba è verde?";

        $this->assertSame(
            'perche-l-erba-e-verde',
            Tag::friendlyTitle(
                $options,
                '-',
                true,
                "'"
            )
        );
    }

    /**
     * Tests Phalcon\Tag :: friendlyTitle() - special characters and escaping
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-11
     */
    public function testFriendlyTitleWithSpecialCharactersAndEscaping(): void
    {
        $options = "Mess'd up --text-- just (to) stress /test/ ?our! "
            . '`little` \\clean\\ url fun.ction!?-->';

        $this->assertSame(
            'messd-up-text-just-to-stress-test-our-little-clean-url-function',
            Tag::friendlyTitle($options)
        );
    }
}
