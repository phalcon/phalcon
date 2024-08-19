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

final class GetValuesTest extends AbstractCollectionTestCase
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
    public function testSupportCollectionGetValues(
        string $class
    ): void {
        $data = $this->getData();
        $collection = new $class($data);

        $expected = $this->getDataValues();
        $actual = $collection->getValues();
        $this->assertSame($expected, $actual);
    }
}
