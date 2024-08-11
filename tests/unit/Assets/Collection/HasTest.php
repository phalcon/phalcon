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

use Phalcon\Assets\Asset;
use Phalcon\Assets\Collection;
use Phalcon\Tests\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\Test;

final class HasTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Assets\Collection :: has()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    #[Test]
    public function testAssetsCollectionHas(): void
    {
        $collection = new Collection();
        $asset1     = new Asset('js', 'js/jquery.js');
        $asset2     = new Asset('js', 'js/jquery-ui.js');

        $collection->add($asset1);

        $this->assertTrue($collection->has($asset1));
        $this->assertFalse($collection->has($asset2));
    }
}
