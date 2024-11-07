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
use Phalcon\Tests\AbstractDatabaseTestCase;

use ReflectionClass;

use function get_class;

final class RemoveTest extends AbstractDatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Statement\Bind :: remove()
     *
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDmQueryBindRemove(): void
    {
        $class = new ReflectionClass(Bind::class);
        $property = $class->getProperty('instanceCount');
        $property->setAccessible(true);
        $property->setValue(0);

        $bind = new Bind();

        $expected = [];
        $actual   = $bind->toArray();
        $this->assertEquals($expected, $actual);

        $bind->bindInline("one");
        $bind->bindInline(true, PDO::PARAM_BOOL);

        $expected = [
            "_1_1_" => ["one", 2],
            "_1_2_" => [1, 5],
        ];
        $actual   = $bind->toArray();
        $this->assertEquals($expected, $actual);

        $bind->remove("_1_1_");

        $expected = [
            "_1_2_" => [1, 5],
        ];
        $actual   = $bind->toArray();
        $this->assertEquals($expected, $actual);
    }
}
