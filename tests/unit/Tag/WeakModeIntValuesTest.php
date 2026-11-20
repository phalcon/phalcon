<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Tag;

use Phalcon\Tag;

/**
 * cphalcon uses the PHP weak type rules for an int value that goes to a string
 * target, thus the int becomes a string.
 *
 * @issue https://github.com/phalcon/phalcon/issues/887
 */
final class WeakModeIntValuesTest extends AbstractTagTestCase
{
    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testTagAppendTitleArrayInt(): void
    {
        Tag::setTitle('Home');
        Tag::appendTitle([2024]);

        $this->assertSame('<title>Home2024</title>' . PHP_EOL, Tag::renderTitle());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testTagPrependTitleArrayInt(): void
    {
        Tag::setTitle('Home');
        Tag::prependTitle([2024]);

        $this->assertSame('<title>2024Home</title>' . PHP_EOL, Tag::renderTitle());
    }
}
