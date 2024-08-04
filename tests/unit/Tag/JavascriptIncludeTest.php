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

class JavascriptIncludeTest extends TagSetup
{
    /**
     * Tests Phalcon\Tag :: javascriptInclude() - string as a parameter local
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-29
     */
    public function testTagJavascriptIncludeLocal(): void
    {
        $options = 'js/phalcon.js';

        Tag::setDocType(
            Tag::HTML5
        );

        $this->assertSame(
            '<script src="/js/phalcon.js"></script>' . PHP_EOL,
            Tag::javascriptInclude($options)
        );
    }

    /**
     * Tests Phalcon\Tag :: javascriptInclude() - array as a parameter local
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-29
     */
    public function testTagJavascriptIncludeWithArrayLocal(): void
    {
        $options = ['js/phalcon.js'];

        Tag::setDocType(
            Tag::HTML5
        );

        $this->assertSame(
            '<script src="/js/phalcon.js"></script>' . PHP_EOL,
            Tag::javascriptInclude($options)
        );
    }

    /**
     * Tests Phalcon\Tag :: javascriptInclude() - remote link
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-29
     */
    public function testTagJavascriptIncludeRemote(): void
    {
        $options = 'https://my.local.com/js/phalcon.js';

        Tag::setDocType(
            Tag::HTML5
        );

        $this->assertSame(
            '<script src="https://my.local.com/js/phalcon.js"></script>' . PHP_EOL,
            Tag::javascriptInclude($options, false)
        );
    }

    /**
     * Tests Phalcon\Tag :: javascriptInclude() - array parameter for a remote
     * link
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-29
     */
    public function testTagJavascriptIncludeWithArrayRemote(): void
    {
        $options = ['https://my.local.com/js/phalcon.js'];

        Tag::setDocType(Tag::HTML5);

        $this->assertSame(
            '<script src="https://my.local.com/js/phalcon.js"></script>' . PHP_EOL,
            Tag::javascriptInclude($options, false)
        );
    }

    /**
     * Tests Phalcon\Tag :: javascriptInclude() - HTML5
     *
     * @issue https://github.com/phalcon/cphalcon/issues/13341
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-12-22
     */
    public function testTagJavascriptIncludeHtml5(): void
    {
        $url = 'js/phalcon.js';

        Tag::setDocType(
            Tag::HTML32
        );

        $this->assertSame(
            '<script type="text/javascript" src="/js/phalcon.js"></script>' . PHP_EOL,
            Tag::javascriptInclude($url)
        );
    }
}
