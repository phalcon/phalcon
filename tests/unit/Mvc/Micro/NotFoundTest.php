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

namespace Phalcon\Tests\Unit\Mvc\Micro;

use Phalcon\Mvc\Micro;
use Phalcon\Tests\Fixtures\Micro\RestHandler;
use Phalcon\Tests\UnitTestCase;

/**
 * Class NotFoundTest extends UnitTestCase
 */
class NotFoundTest extends UnitTestCase
{
    /**
     * Tests the notFound
     *
     * @issue  T169
     * @author Nikos Dimopoulos <nikos@niden.net>
     * @since  2012-11-06
     */
    public function testMicroNotFoundT169(): void
    {
        $handler = new RestHandler();

        $app = new Micro();

        $app->get('/api/site', [$handler, 'find']);
        $app->post('/api/site/save', [$handler, 'save']);

        $flag = false;

        $app->notFound(
            function () use (&$flag) {
                $flag = true;
            }
        );

        $_SERVER['REQUEST_METHOD'] = 'GET';

        $app->handle('/fourohfour');

        $this->assertTrue($flag);
    }
}
