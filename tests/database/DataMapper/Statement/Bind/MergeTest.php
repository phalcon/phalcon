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

use function get_class;

final class MergeTest extends AbstractDatabaseTestCase
{

    /**
     * Database Tests Phalcon\DataMapper\Statement\Bind :: merge()
     *
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDmQueryBindMerge()
    {
        $bind = new Bind();
        $bind->merge(['key' => 'value']);

        $this->assertArrayHasKey('key', $bind->toArray());
    }
}
