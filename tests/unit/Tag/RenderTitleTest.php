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
use Phalcon\Tests\AbstractUnitTestCase;

class RenderTitleTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Tag :: renderTitle() - with malicious code
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-01
     */
    public function testTagRenderTitleWithMaliciousContent(): void
    {
        Tag::resetInput();

        $value = "Hello </title><script>alert('Got your nose!');</script><title>";

        Tag::setTitle($value);

        $this->assertSame(
            '<title>Hello &lt;/title&gt;&lt;script&gt;alert(&#039;' .
            'Got your nose!&#039;);&lt;/script&gt;&lt;title&gt;</title>' . PHP_EOL,
            Tag::renderTitle()
        );
    }
}
