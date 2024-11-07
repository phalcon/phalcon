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
use Phalcon\DataMapper\Statement\Select;
use Phalcon\Tests\AbstractDatabaseTestCase;
use ReflectionClass;

use function get_class;

final class GetTypeTest extends AbstractDatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Statement\Bind :: getType()
     *
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDmQueryBindGetType()
    {
        $bind = new Bind();

        $expected = PDO::PARAM_NULL;
        $actual   = $this->invokeMethod($bind, 'getType', [null]);
        $this->assertSame($expected, $actual);

        $expected = PDO::PARAM_BOOL;
        $actual   = $this->invokeMethod($bind, 'getType', [true]);
        $this->assertSame($expected, $actual);

        $expected = PDO::PARAM_INT;
        $actual   = $this->invokeMethod($bind, 'getType', [1]);
        $this->assertSame($expected, $actual);

        $expected = PDO::PARAM_STR;
        $actual   = $this->invokeMethod($bind, 'getType', ['string']);
        $this->assertSame($expected, $actual);
    }
}
