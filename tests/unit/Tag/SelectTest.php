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

use PHPUnit\Framework\Attributes\Test;

class SelectTest extends AbstractTagSetup
{
    /**
     * Tests Phalcon\Tag :: select()
     *
     * @author Cameron Hall <me@chall.id.au>
     * @since  2019-01-27
     */
    #[Test]
    public function testTagSelect(): void
    {
        $this->testFieldParameter(
            'select',
            [
                'potato',
                [
                    'Phalcon',
                    'PHP',
                ],
            ],
            "<select id=\"potato\" name=\"potato\">" . PHP_EOL . "\t" .
            "<option value=\"0\">Phalcon</option>" . PHP_EOL . "\t<option value=\"1\">" .
            "PHP</option>" . PHP_EOL . "</select"
        );
    }

    /**
     * Tests Phalcon\Tag :: select() with no options
     *
     * @author Cameron Hall <me@chall.id.au>
     * @since  2019-01-27
     *
     * @issue https://github.com/phalcon/cphalcon/issues/13352
     */
    #[Test]
    public function testTagSelectWithNoOptions(): void
    {
        $this->testFieldParameter(
            'select',
            [
                'potato',
            ],
            "<select id=\"potato\" name=\"potato\">" . PHP_EOL . "</select"
        );
    }
}
