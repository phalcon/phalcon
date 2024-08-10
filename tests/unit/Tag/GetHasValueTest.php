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

use function uniqid;

class GetHasValueTest extends AbstractTagSetup
{
    /**
     * Tests Phalcon\Tag :: getValue()/hasValue()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-02-22
     */
    public function testTagGetHasValue(): void
    {
        $post = $_POST;

        $key   = uniqid('key-');
        $value = uniqid('val-');

        $actual = Tag::getValue($key, ['value' => $value]);
        $this->assertSame($value, $actual);

        $key   = uniqid('key-');
        $value = uniqid('val-');

        Tag::setDefault($key, $value);

        $key   = uniqid('key-');
        $value = uniqid('val-');

        $_POST = [
            $key => $value,
        ];

        $actual = Tag::getValue($key, []);
        $this->assertSame($value, $actual);

        $_POST = [];

        $key = uniqid('key-');

        $actual = Tag::getValue($key, []);
        $this->assertNull($actual);

        $_POST = $post;
    }
}
