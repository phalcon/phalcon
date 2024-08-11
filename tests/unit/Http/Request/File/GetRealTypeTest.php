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

use Phalcon\Http\Request\File;
use Phalcon\Tests\Fixtures\Page\Http;
use Phalcon\Tests\AbstractUnitTestCase;

use function dataDir;
use function function_exists;

final class GetRealTypeTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Request\File :: getRealType()
     *
     * @issue  https://github.com/phalcon/cphalcon/issues/1442
     * @author Phalcon Team <team@phalcon.io>
     * @author Dreamszhu <dreamsxin@qq.com>
     * @since  2013-10-26
     */
    public function testHttpRequestFileGetRealType(): void
    {
        if (!function_exists('finfo_open')) {
            $this->markTestSkipped('fileinfo extension missing');
        }

        $file = new File(
            [
                'name'     => 'test',
                'type'     => Http::CONTENT_TYPE_PLAIN,
                'tmp_name' => dataDir('/assets/images/example-jpg.jpg'),
                'size'     => 1,
                'error'    => 0,
            ]
        );

        $expected = 'image/jpeg';
        $actual   = $file->getRealType();
        $this->assertSame($expected, $actual);
    }
}
