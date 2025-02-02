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

namespace Phalcon\Tests\Database\DataMapper\Statement\Select;

use Phalcon\DataMapper\Statement\Select;
use Phalcon\Tests\AbstractStatementTestCase;

use function env;

final class ConstructTest extends AbstractStatementTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Statement\Select :: __construct()
     *
     * @since  2020-01-20
     *
     * @group mysql
     */
    public function testDmStatementSelectConstruct(): void
    {
        $driver = env('driver');
        $select = Select::new($driver);

        $this->assertInstanceOf(Select::class, $select);
    }
}
