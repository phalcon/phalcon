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
use Phalcon\Tests\Fixtures\Support\Collection\CollectionJsonEncodeFixture;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

final class ToJsonTest extends AbstractCollectionTestCase
{
    /**
     * Tests Phalcon\Support\Collection :: toJson()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    #[Test]
    #[DataProvider('getClasses')]
    public function testSupportCollectionToJson(
        string $class
    ): void {
        $data = $this->getData();
        $collection = new $class($data);

        $expected = json_encode($data);
        $actual   = $collection->toJson();
        $this->assertSame($expected, $actual);

        $expected = json_encode($data, JSON_PRETTY_PRINT);
        $actual   = $collection->toJson(JSON_PRETTY_PRINT);
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Support\Collection :: toJson() - encode fail
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    #[Test]
    public function testSupportCollectionToJsonEncodeFail(): void
    {
        $data = $this->getData();
        $collection = new CollectionJsonEncodeFixture($data);

        $actual = $collection->toJson();
        $this->assertEmpty($actual);
    }
}
