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

namespace Phalcon\Tests\Unit\Support\Collection;

use Phalcon\Support\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

final class GetKeysTest extends AbstractCollectionTestCase
{
    /**
     * Tests Phalcon\Support\Collection :: get()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    #[Test]
    #[DataProvider('getClasses')]
    public function testSupportCollectionGetKeys(): void
    {
        $data = $this->getData();
        $collection = new Collection($data);

        $expected = $this->getDataKeys();
        $actual   = $collection->getKeys();
        $this->assertSame($expected, $actual);

        $data = $this->getDataNoCase();
        $collection = new Collection($data);
        $expected   = $this->getDataKeys();
        $actual     = $collection->getKeys();
        $this->assertSame($expected, $actual);

        $expected = array_keys($data);
        $actual   = $collection->getKeys(false);
        $this->assertSame($expected, $actual);
    }
}
