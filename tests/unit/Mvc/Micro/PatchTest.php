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
use Phalcon\Tests\AbstractUnitTestCase;

class PatchTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Mvc\Micro :: patch()
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-04-17
     */
    public function testMvcMicroPatch(): void
    {
        $micro = new Micro();

        $micro->get(
            '/test',
            function () {
                return 'this is get';
            }
        );

        $micro->patch(
            '/test',
            function () {
                return 'this is patch';
            }
        );

        $micro->post(
            '/test',
            function () {
                return 'this is post';
            }
        );


        $_SERVER['REQUEST_METHOD'] = 'PATCH';

        // Micro echoes out its result as well
        ob_start();
        $result = $micro->handle('/test');
        ob_end_clean();

        $this->assertEquals(
            'this is patch',
            $result
        );
    }
}
