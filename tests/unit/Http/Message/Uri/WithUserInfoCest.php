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

use Codeception\Example;
use Phalcon\Http\Message\Uri;
use UnitTester;
use function sprintf;

class WithUserInfoCest
{
    /**
     * Tests Phalcon\Http\Message\Uri :: withUserInfo()
     *
     * @dataProvider getExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2019-02-09
     */
    public function httpMessageUriWithUserInfo(UnitTester $I, Example $example)
    {
        $I->wantToTest(
            'Http\Message\Uri - withUserInfo() - ' . $example['label']
        );

        $query = 'https://%s@dev.phalcon.ld:8080/action?param=value#frag';
        $uri   = new Uri(sprintf($query, 'zephir:module'));

        $user = $example['user'];
        $pass = $example['pass'];

        $newInstance = $uri->withUserInfo($user, $pass);
        $I->assertNotSame($uri, $newInstance);

        $expected = $example['expected'];
        $actual   = $newInstance->getUserInfo();
        $I->assertSame($expected, $actual);

        $expected = sprintf($query, $example['expected']);
        $actual   = (string) $newInstance;
        $I->assertSame($expected, $actual);
    }

    /**
     * @return string[][]
     */
    private function getExamples(): array
    {
        return [
            [
                'label'    => 'valid',
                'user'     => 'phalcon',
                'pass'     => 'secret',
                'expected' => 'phalcon:secret',
            ],
            [
                'label'    => 'user only',
                'user'     => 'phalcon',
                'pass'     => null,
                'expected' => 'phalcon',
            ],
            [
                'label'    => 'user only empty pass',
                'user'     => 'phalcon',
                'pass'     => '',
                'expected' => 'phalcon:',
            ],
            [
                'label'    => 'user with @',
                'user'     => 'phalcon@secret',
                'pass'     => 'secret@phalcon',
                'expected' => 'phalcon%40secret:secret%40phalcon',
            ],
            [
                'label'    => 'user with :',
                'user'     => 'phalcon:secret',
                'pass'     => 'secret:phalcon',
                'expected' => 'phalcon%3Asecret:secret%3Aphalcon',
            ],
            [
                'label'    => 'user with %',
                'user'     => 'phalcon%secret',
                'pass'     => 'secret%phalcon',
                'expected' => 'phalcon%25secret:secret%25phalcon',
            ],
            [
                'label'    => 'user invalid UTF8',
                'user'     => "\x21\x92",
                'pass'     => '!?',
                'expected' => '!%92:!%3F',

                -'!%92:!%3F'
                + '%21�:!%3F',


            ],
            [
                'label'    => 'user invalid encoding',
                'user'     => '%ZZ',
                'pass'     => '%GG',
                'expected' => '%25ZZ:%25GG',
            ],
        ];
    }
}
