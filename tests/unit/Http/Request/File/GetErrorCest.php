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

namespace Phalcon\Tests\Unit\Http\Request\File;

use Page\Http;
use Phalcon\Http\Request\File;
use UnitTester;

use function dataDir;

class GetErrorCest
{
    /**
     * Tests Phalcon\Http\Request\File :: getError()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-17
     */
    public function httpRequestFileGetError(UnitTester $I)
    {
        $I->wantToTest('Http\Request\File - getError()');

        $file = new File(
            [
                'name'     => 'test',
                'type'     => Http::CONTENT_TYPE_PLAIN,
                'tmp_name' => dataDir('/assets/images/example-jpg.jpg'),
                'size'     => 1,
                'error'    => 0,
            ]
        );

        $expected = 0;
        $actual   = $file->getError();
        $I->assertSame($expected, $actual);
    }
}
