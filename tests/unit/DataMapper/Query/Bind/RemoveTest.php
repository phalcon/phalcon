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

namespace Phalcon\Tests\Unit\DataMapper\Query\Bind;

use Phalcon\Tests\DatabaseTestCase;
use PDO;
use Phalcon\DataMapper\Query\Bind;

final class RemoveTest extends DatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Query\Bind :: remove()
     *
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDmQueryBindRemove(): void
    {
        $bind = new Bind();

        $expected = [];
        $actual   = $bind->toArray();
        $this->assertEquals($expected, $actual);

        $bind->bindInline("one");
        $bind->bindInline(true, PDO::PARAM_BOOL);

        $expected = [
            "__1__" => ["one", 2],
            "__2__" => [1, 5],
        ];
        $actual   = $bind->toArray();
        $this->assertEquals($expected, $actual);

        $bind->remove("__1__");

        $expected = [
            "__2__" => [1, 5],
        ];
        $actual   = $bind->toArray();
        $this->assertEquals($expected, $actual);
    }
}
