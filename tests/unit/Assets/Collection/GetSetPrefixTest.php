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
use Phalcon\Tests\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\Test;

final class GetSetPrefixTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Assets\Collection :: getPrefix() / setPrefix()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    #[Test]
    public function testAssetsCollectionGetSetPrefix(): void
    {
        $collection = new Collection();
        $prefix     = 'phly_';
        $collection->setPrefix($prefix);

        $this->assertSame($prefix, $collection->getPrefix());
    }
}
