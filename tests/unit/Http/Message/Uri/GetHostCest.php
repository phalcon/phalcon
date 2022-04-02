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

namespace Phalcon\Tests\Unit\Http\Message\Uri;

use Phalcon\Http\Message\Uri;
use UnitTester;

class GetHostCest
{
    /**
     * Tests Phalcon\Http\Message\Uri :: getHost()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-09
     */
    public function httpMessageUriGetHost(UnitTester $I)
    {
        $I->wantToTest('Http\Message\Uri - getHost()');

        $query = 'https://phalcon:secret@dev.phalcon.ld:8080/action?param=value#frag';
        $uri   = new Uri($query);

        $expected = 'dev.phalcon.ld';
        $actual   = $uri->getHost();
        $I->assertSame($expected, $actual);
    }
}
