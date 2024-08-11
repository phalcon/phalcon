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
use Phalcon\Assets\Filters\None;
use Phalcon\Tests\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\Test;

final class AddFilterTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Assets\Collection :: addFilter()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    #[Test]
    public function testAssetsCollectionAddFilter(): void
    {
        $collection = new Collection();
        $collection->addFilter(new None());
        $collection->addFilter(new None());

        $filters = $collection->getFilters();

        foreach ($filters as $filter) {
            $this->assertInstanceOf(None::class, $filter);
        }

        $this->assertCount(2, $filters);
    }
}
