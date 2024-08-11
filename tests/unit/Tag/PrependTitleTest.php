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

class PrependTitleTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Tag :: prependTitle()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2012-09-05
     */
    public function testTagPrependTitle(): void
    {
        Tag::resetInput();

        Tag::setTitle('Title');

        Tag::prependTitle('Class');

        $this->assertSame(
            'Title',
            Tag::getTitle(false, false)
        );

        $this->assertSame(
            'ClassTitle',
            Tag::getTitle(true, false)
        );

        $this->assertSame(
            '<title>ClassTitle</title>' . PHP_EOL,
            Tag::renderTitle()
        );
    }

    /**
     * Tests Phalcon\Tag :: prependTitle() - array
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2012-09-05
     */
    public function testTagPrependTitleArray(): void
    {
        Tag::resetInput();

        Tag::setTitle('Main');
        Tag::setTitleSeparator(' - ');

        Tag::prependTitle(
            ['Category', 'Title']
        );

        $this->assertSame(
            'Main',
            Tag::getTitle(false, false)
        );

        $this->assertSame(
            'Title - Category - Main',
            Tag::getTitle(true, false)
        );

        $this->assertSame(
            '<title>Title - Category - Main</title>' . PHP_EOL,
            Tag::renderTitle()
        );
    }

    /**
     * Tests Phalcon\Tag :: prependTitle() - double call
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2012-09-05
     */
    public function testTagPrependTitleDoubleCall(): void
    {
        Tag::resetInput();

        Tag::setTitle('Main');
        Tag::setTitleSeparator(' - ');

        Tag::prependTitle('Category');
        Tag::prependTitle('Title');

        $this->assertSame(
            'Main',
            Tag::getTitle(false, false)
        );

        $this->assertSame(
            'Title - Category - Main',
            Tag::getTitle(true, false)
        );

        $this->assertSame(
            '<title>Title - Category - Main</title>' . PHP_EOL,
            Tag::renderTitle()
        );
    }

    /**
     * Tests Phalcon\Tag :: prependTitle() - empty array
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2012-09-05
     */
    public function testTagPrependTitleEmptyArray(): void
    {
        Tag::resetInput();

        Tag::setTitle('Main');
        Tag::setTitleSeparator(' - ');

        Tag::prependTitle('Category');

        Tag::prependTitle(
            []
        );

        $this->assertSame(
            'Main',
            Tag::getTitle(false, false)
        );

        $this->assertSame(
            'Main',
            Tag::getTitle(true, false)
        );

        $this->assertSame(
            '<title>Main</title>' . PHP_EOL,
            Tag::renderTitle()
        );
    }

    /**
     * Tests Phalcon\Tag :: prependTitle() - separator
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2012-09-05
     */
    public function testTagPrependTitleSeparator(): void
    {
        Tag::resetInput();

        Tag::setTitle('Title');
        Tag::setTitleSeparator('|');

        Tag::prependTitle('Class');

        $this->assertSame(
            'Title',
            Tag::getTitle(false, false)
        );

        $this->assertSame(
            'Class|Title',
            Tag::getTitle(true, false)
        );

        $this->assertSame(
            '<title>Class|Title</title>' . PHP_EOL,
            Tag::renderTitle()
        );
    }
}
