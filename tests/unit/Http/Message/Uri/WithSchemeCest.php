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

use InvalidArgumentException;
use Phalcon\Http\Message\Uri;
use UnitTester;

use function sprintf;

class WithSchemeCest
{
    /**
     * Tests Phalcon\Http\Message\Uri :: withScheme()
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2019-02-09
     */
    public function httpMessageUriWithScheme(UnitTester $I)
    {
        $I->wantToTest('Http\Message\Uri - withScheme()');

        $query = '%s://phalcon:secret@dev.phalcon.ld:8000/action?param=value#frag';

        $uri = new Uri(
            sprintf($query, 'https')
        );

        $newInstance = $uri->withScheme('http');
        $I->assertNotSame($uri, $newInstance);

        $example = "http";
        $actual  = $newInstance->getScheme();
        $I->assertSame($example, $actual);

        $example = sprintf($query, 'http');
        $actual  = (string)$newInstance;
        $I->assertSame($example, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\Uri :: withScheme() - exception unsupported
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2019-06-01
     */
    public function httpUriWithSchemeExceptionUnsupported(UnitTester $I)
    {
        $I->wantToTest('Http\Uri - withScheme() - exception - unsupported');

        $I->expectThrowable(
            new InvalidArgumentException(
                'Unsupported scheme [ftp]. Scheme must be one of [http, https]'
            ),
            function () {
                $uri = new Uri(
                    'https://phalcon:secret@dev.phalcon.ld:8080/action?param=value#frag'
                );

                $instance = $uri->withScheme('ftp');
            }
        );
    }
}
