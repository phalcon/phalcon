<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Html\Helper\Input\Select;

use Phalcon\Html\Helper\Input\Select\ArrayData;
use Phalcon\Tests\AbstractUnitTestCase;

final class ArrayDataTest extends AbstractUnitTestCase
{
    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-17
     */
    public function testGetOptionsReturnsFlatArray(): void
    {
        $arrayData = new ArrayData(['1' => 'Ferrari', '2' => 'Ford']);
        $this->assertSame(['1' => 'Ferrari', '2' => 'Ford'], $arrayData->getOptions());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-17
     */
    public function testGetOptionsReturnsNestedArrayForOptgroups(): void
    {
        $input = [
            'Group A' => ['1' => 'Ferrari', '2' => 'Ford'],
            '3' => 'Toyota',
        ];
        $arrayData = new ArrayData($input);
        $this->assertSame($input, $arrayData->getOptions());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-17
     */
    public function testGetOptionsReturnsEmptyArrayWhenNoData(): void
    {
        $arrayData = new ArrayData();
        $this->assertSame([], $arrayData->getOptions());
    }
}
