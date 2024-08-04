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

namespace Phalcon\Tests\Unit\Assets\Collection;

use Phalcon\Assets\Collection;
use Phalcon\Tests\UnitTestCase;

final class GetSetAttributesTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Assets\Collection :: getAttributes() / setAttributes()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testAssetsCollectionGetSetAttributes(): void
    {
        $collection = new Collection();
        $attributes = [
            'data-name' => 'phalcon',
            'data-type' => 'book',
        ];

        $this->assertSame([], $collection->getAttributes());
        $collection->setAttributes($attributes);

        $this->assertSame($attributes, $collection->getAttributes());
    }
}
