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

final class AddInlineJsTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Assets\Collection :: addInlineJs()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    #[Test]
    public function testAssetsCollectionAddInlineJs(): void
    {
        $collection = new Collection();
        $collection->addInlineJs("alert('an amazing test');");

        $codes = $collection->getCodes();

        $this->assertCount(1, $codes);
    }
}
