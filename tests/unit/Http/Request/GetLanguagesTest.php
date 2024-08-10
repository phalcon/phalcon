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

use Phalcon\Tests\Unit\Http\Helper\AbstractHttpBase;

final class GetLanguagesTest extends AbstractHttpBase
{
    /**
     * Tests Phalcon\Http\Request :: getLanguages()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-17
     */
    public function testHttpRequestGetLanguages(): void
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'es,es-ar;q=0.8,en;q=0.5,en-us;q=0.3,de-de; q=0.9';

        $request = $this->getRequestObject();

        $expected = [
            [
                'language' => 'es',
                'quality'  => 1.0,
            ],
            [
                'language' => 'es-ar',
                'quality'  => 0.8,
            ],
            [
                'language' => 'en',
                'quality'  => 0.5,
            ],
            [
                'language' => 'en-us',
                'quality'  => 0.3,
            ],
            [
                'language' => 'de-de',
                'quality'  => 0.9,
            ],
        ];
        $actual   = $request->getLanguages();
        $this->assertSame($expected, $actual);
    }
}
