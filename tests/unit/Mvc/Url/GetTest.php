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

namespace Phalcon\Tests\Unit\Mvc\Url;

use Phalcon\Mvc\Router;
use Phalcon\Mvc\Url;
use Phalcon\Tests\AbstractUnitTestCase;

final class GetTest extends AbstractUnitTestCase
{
    /**
     * @return array
     */
    public static function getExamples(): array
    {
        return [
            [
                'https://phalcon.io',
                null,
            ],
            [
                'https://phalcon.io',
                '',
            ],
            [
                'https://phalcon.io/',
                '/',
            ],
            [
                'https://phalcon.io/en/team',
                '/en/team',
            ],
        ];
    }

    /**
     * @dataProvider getExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2018-11-13
     */
    public function testMvcUrlGet(
        string $expected,
        ?string $name
    ): void {
        $url = new Url();

        $url->setBaseUri('https://phalcon.io');

        $actual = $url->get($name);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-05-13
     * @issue  https://github.com/phalcon/cphalcon/issues/17007
     */
    public function testGetUsesProtocolRelativeUrlWhenRouteHasHostname(): void
    {
        $router = new Router(false);
        $router->add('/login', [
            'module'     => 'account',
            'controller' => 'auth',
            'action'     => 'login',
        ])->setHostname('account.company.com')
          ->setName('account_login');

        $url = new Url($router);
        $url->setBaseUri('/');

        $expected = '//account.company.com/login';
        $actual   = $url->get(['for' => 'account_login']);

        $this->assertSame($expected, $actual);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-05-13
     * @issue  https://github.com/phalcon/cphalcon/issues/17007
     */
    public function testGetIgnoresHostnameWhenRouteHasNone(): void
    {
        $router = new Router(false);
        $router->add('/about', [
            'controller' => 'pages',
            'action'     => 'about',
        ])->setName('about');

        $url = new Url($router);
        $url->setBaseUri('/');

        $expected = '/about';
        $actual   = $url->get(['for' => 'about']);

        $this->assertSame($expected, $actual);
    }
}
