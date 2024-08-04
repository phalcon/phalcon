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

class SetTitleTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Tag :: setTitle()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testTagSetTitle(): void
    {
        Tag::resetInput();

        $value = 'This is my title';

        Tag::setTitle($value);

        $this->assertSame(
            "<title>{$value}</title>" . PHP_EOL,
            Tag::renderTitle()
        );

        $this->assertSame(
            "{$value}",
            Tag::getTitle()
        );
    }
}
