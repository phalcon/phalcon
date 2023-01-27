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

namespace Phalcon\Tests\Unit\Http\Request;

use Page\Http;
use Phalcon\Tests\Unit\Http\Helper\HttpBase;
use UnitTester;

class HasFilesCest extends HttpBase
{
    /**
     * Tests Request::hasFiles
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2016-01-31
     */
    public function testRequestHasFiles(UnitTester $I)
    {
        $request = $this->getRequestObject();

        $actual = $request->hasFiles();
        $I->assertFalse($actual);

        $_FILES = [
            'test' => [
                'name'     => 'name',
                'type'     => Http::CONTENT_TYPE_PLAIN,
                'size'     => 1,
                'tmp_name' => 'tmp_name',
                'error'    => 0,
            ],
        ];

        $actual = $request->hasFiles();
        $I->assertTrue($actual);
    }
}
