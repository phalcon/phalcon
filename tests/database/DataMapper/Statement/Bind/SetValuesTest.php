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

namespace Phalcon\Tests\Database\DataMapper\Statement\Bind;

use Phalcon\DataMapper\Statement\Bind;
use Phalcon\Tests\AbstractStatementTestCase;

final class SetValuesTest extends AbstractStatementTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Statement\Bind :: setValues()
     *
     * @since  2020-01-20
     *
     * @group mysql
     */
    public function testDmStatementBindSetValues(): void
    {
        $bind = new Bind();

        $expected = [];
        $actual   = $bind->toArray();
        $this->assertSame($expected, $actual);

        $bind->setValues(
            [
                'one'   => 'two',
                'three' => 'four',
                'five'  => ['six', 'seven', 8, 9],
            ]
        );

        $bind->setValue('nine', null);
        $bind->setValue('ten', false);
        $bind->setValue('eleven', 11);

        $expected = [
            'one'    => ['two', 2],
            'three'  => ['four', 2],
            'five'   => [['six', 'seven', 8, 9], 2],
            'nine'   => [null, 0],
            'ten'    => [false, 5],
            'eleven' => [11, 1],
        ];
        $actual   = $bind->toArray();
        $this->assertSame($expected, $actual);
    }
}
