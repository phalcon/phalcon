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

namespace Phalcon\Tests\Unit\Html\Attributes;

use Phalcon\Html\Attributes;
use Phalcon\Tests\UnitTestCase;

final class CountTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Html\Attributes :: count()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-06-02
     */
    public function testHtmlAttributesCount(): void
    {
        $data = [
            'type'  => 'text',
            'class' => 'form-control',
            'name'  => 'q',
            'value' => '',
        ];

        $attributes = new Attributes($data);

        $this->assertCount(
            4,
            $attributes->toArray()
        );

        $this->assertSame(
            4,
            $attributes->count()
        );
    }
}
