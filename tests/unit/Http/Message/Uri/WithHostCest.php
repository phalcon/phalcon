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

use function sprintf;

class WithHostCest
{
    /**
     * Tests Phalcon\Http\Message\Uri :: withHost()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-09
     */
    public function httpMessageUriWithHost(UnitTester $I)
    {
        $I->wantToTest('Http\Message\Uri - withHost()');

        $query = 'https://phalcon:secret@%s:8080/action?param=value#frag';

        $uri = new Uri(
            sprintf($query, 'dev.phalcon.ld')
        );

        $newInstance = $uri->withHost('prod.phalcon.ld');

        $I->assertNotSame($uri, $newInstance);

        $expected = 'prod.phalcon.ld';
        $actual   = $newInstance->getHost();
        $I->assertSame($expected, $actual);

        $expected = sprintf($query, 'prod.phalcon.ld');
        $actual   = (string) $newInstance;
        $I->assertSame($expected, $actual);
    }
}
