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

namespace Phalcon\Tests\Unit\Mvc\Micro\Collection;

use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Micro\Collection;
use Phalcon\Tests\Fixtures\Micro\HttpMethodHandler;
use Phalcon\Tests\UnitTestCase;

class HeadTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Mvc\Micro\Collection :: head()
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-05-22
     */
    public function testMvcMicroCollectionHead(): void
    {
        $micro = new Micro();

        $collection = new Collection();

        $httpMethodHandler = new HttpMethodHandler();

        $collection->setHandler($httpMethodHandler);

        $collection->get('/test', 'get');
        $collection->post('/test', 'post');
        $collection->head('/test', 'head');

        $micro->mount($collection);


        $_SERVER['REQUEST_METHOD'] = 'HEAD';

        // Micro echoes out its result as well
        ob_start();
        $result = $micro->handle('/test');
        ob_end_clean();

        $this->assertEquals(
            'this is head',
            $result
        );
    }
}