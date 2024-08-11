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

final class AddTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Assets\Collection :: add()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    #[Test]
    public function testAssetsCollectionAdd(): void
    {
        $collection = new Collection();
        $collection->add(new Asset('js', 'js/jquery.js'));
        $collection->add(new Asset('js', 'js/jquery-ui.js'));

        $expected = 'js';
        foreach ($collection as $asset) {
            $actual = $asset->getType();
            $this->assertSame($expected, $actual);
        }

        $this->assertCount(2, $collection);
    }

    /**
     * Tests Phalcon\Assets\Collection :: add() - duplicate
     *
     * @issue  https://github.com/phalcon/cphalcon/issues/10938
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    #[Test]
    public function testAssetsCollectionAddDuplicate(): void
    {
        $collection = new Collection();

        for ($counter = 0; $counter < 10; $counter++) {
            $collection->add(new Asset('js', 'js/jquery.js'));
            $collection->add(new Asset('js', 'js/jquery-ui.js'));
        }

        $this->assertCount(2, $collection->getAssets());
    }
}
