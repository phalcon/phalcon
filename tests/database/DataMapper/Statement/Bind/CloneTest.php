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

use PDO;
use Phalcon\DataMapper\Statement\Bind;
use Phalcon\Tests\AbstractStatementTestCase;

final class CloneTest extends AbstractStatementTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Statement\Bind :: clone()
     *
     * @since  2020-01-20
     *
     * @group mysql
     */
    public function testDmStatementBindClone(): void
    {
        $bind = new Bind();

        $expected = [];
        $actual   = $bind->toArray();
        $this->assertSame($expected, $actual);

        $bind->inline('one');
        $bind->inline(true, PDO::PARAM_BOOL);

        $expected = [
            '_1_1_' => ['one', 2],
            '_1_2_' => [true, 5],
        ];
        $actual   = $bind->toArray();
        $this->assertSame($expected, $actual);

        $clone = clone $bind;

        $clone->inline('two');

        $expected = [
            '_1_1_' => ['one', 2],
            '_1_2_' => [true, 5],
            '_2_3_' => ['two', 2],
        ];
        $actual   = $clone->toArray();
        $this->assertSame($expected, $actual);
    }
}
