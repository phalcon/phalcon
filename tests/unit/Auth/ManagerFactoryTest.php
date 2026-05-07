<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Implementation of this file has been influenced by sinbadxiii/cphalcon-auth
 * @link    https://github.com/sinbadxiii/cphalcon-auth
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Auth;

use Phalcon\Auth\Access\Auth;
use Phalcon\Auth\Guard\Session;
use Phalcon\Auth\ManagerFactory;
use Phalcon\Config\Config;
use Phalcon\Encryption\Security;
use Phalcon\Tests\AbstractUnitTestCase;
use Phalcon\Tests\Unit\Auth\Fake\FakeCookies;
use Phalcon\Tests\Unit\Auth\Fake\FakeRequest;
use Phalcon\Tests\Unit\Auth\Fake\FakeSessionManager;

final class ManagerFactoryTest extends AbstractUnitTestCase
{
    private Security $security;

    protected function setUp(): void
    {
        $this->security = new Security();
    }

    private function singleSessionConfig(): array
    {
        return [
            'guards' => [
                'web' => [
                    'type'    => 'session',
                    'default' => true,
                    'adapter' => [
                        'name'    => 'memory',
                        'options' => [
                            'users' => [
                                [
                                    'id'       => 1,
                                    'email'    => 'alice@example.com',
                                    'password' => $this->security->hash('secret'),
                                ],
                            ],
                        ],
                    ],
                    'options' => [],
                    'extra'   => [
                        new FakeRequest(),
                        new FakeCookies(),
                        new FakeSessionManager(),
                    ],
                ],
            ],
        ];
    }

    public function testLoadAcceptsConfigInterface(): void
    {
        $config  = new Config($this->singleSessionConfig());
        $factory = new ManagerFactory($this->security);
        $manager = $factory->load($config);

        $this->assertInstanceOf(Session::class, $manager->getDefaultGuard());
    }

    public function testLoadIgnoresMissingAccess(): void
    {
        $factory = new ManagerFactory($this->security);
        $manager = $factory->load($this->singleSessionConfig());

        $this->assertSame([], $manager->getAccessList());
    }

    public function testLoadSetsAccessListWhenProvided(): void
    {
        $config          = $this->singleSessionConfig();
        $config['access'] = [
            'auth' => Auth::class,
        ];

        $factory = new ManagerFactory($this->security);
        $manager = $factory->load($config);

        $this->assertArrayHasKey('auth', $manager->getAccessList());
        $this->assertSame(Auth::class, $manager->getAccessList()['auth']);
    }

    public function testLoadWithMultipleGuards(): void
    {
        $fakeRequest = new FakeRequest();

        $config = [
            'guards' => [
                'web' => [
                    'type'    => 'session',
                    'default' => true,
                    'adapter' => [
                        'name'    => 'memory',
                        'options' => [
                            'users' => [
                                [
                                    'id'       => 1,
                                    'email'    => 'alice@example.com',
                                    'password' => $this->security->hash('secret'),
                                ],
                            ],
                        ],
                    ],
                    'options' => [],
                    'extra'   => [
                        new FakeRequest(),
                        new FakeCookies(),
                        new FakeSessionManager(),
                    ],
                ],
                'api' => [
                    'type'    => 'token',
                    'adapter' => [
                        'name'    => 'memory',
                        'options' => [
                            'users' => [
                                [
                                    'id'        => 2,
                                    'email'     => 'bob@example.com',
                                    'password'  => $this->security->hash('token123'),
                                    'api_token' => 'mytoken',
                                ],
                            ],
                        ],
                    ],
                    'options' => [
                        'inputKey'   => 'api_token',
                        'storageKey' => 'api_token',
                    ],
                    'extra' => [$fakeRequest],
                ],
            ],
        ];

        $factory = new ManagerFactory($this->security);
        $manager = $factory->load($config);

        $guards = $manager->getGuards();

        $this->assertArrayHasKey('web', $guards);
        $this->assertArrayHasKey('api', $guards);
        $this->assertInstanceOf(Session::class, $manager->getDefaultGuard());
    }

    public function testLoadWithSingleSessionGuard(): void
    {
        $factory = new ManagerFactory($this->security);
        $manager = $factory->load($this->singleSessionConfig());

        $this->assertInstanceOf(Session::class, $manager->getDefaultGuard());
    }

    public function testLoadWithStreamAdapter(): void
    {
        $factory = new ManagerFactory($this->security);
        $manager = $factory->load([
            'guards' => [
                'web' => [
                    'type'    => 'session',
                    'default' => true,
                    'adapter' => [
                        'name'    => 'stream',
                        'options' => ['file' => '/tmp/users.json'],
                    ],
                    'options' => [],
                    'extra'   => [
                        new FakeRequest(),
                        new FakeCookies(),
                        new FakeSessionManager(),
                    ],
                ],
            ],
        ]);

        $this->assertInstanceOf(Session::class, $manager->getDefaultGuard());
    }

    public function testLoadWithModelAdapter(): void
    {
        $factory = new ManagerFactory($this->security);
        $manager = $factory->load([
            'guards' => [
                'web' => [
                    'type'    => 'session',
                    'default' => true,
                    'adapter' => [
                        'name'    => 'model',
                        'options' => ['model' => 'App\\Models\\User'],
                    ],
                    'options' => [],
                    'extra'   => [
                        new FakeRequest(),
                        new FakeCookies(),
                        new FakeSessionManager(),
                    ],
                ],
            ],
        ]);

        $this->assertInstanceOf(Session::class, $manager->getDefaultGuard());
    }

    public function testLoadWithTokenGuard(): void
    {
        $factory = new ManagerFactory($this->security);
        $manager = $factory->load([
            'guards' => [
                'api' => [
                    'type'    => 'token',
                    'default' => true,
                    'adapter' => [
                        'name'    => 'memory',
                        'options' => ['users' => []],
                    ],
                    'options' => [
                        'inputKey'   => 'api_token',
                        'storageKey' => 'api_token',
                    ],
                    'extra' => [new FakeRequest()],
                ],
            ],
        ]);

        $this->assertInstanceOf(\Phalcon\Auth\Guard\Token::class, $manager->getDefaultGuard());
    }

    public function testLoadThrowsForUnknownAdapter(): void
    {
        $this->expectException(\Phalcon\Auth\Exception::class);
        $this->expectExceptionMessageMatches('/Unknown auth adapter/');

        $factory = new ManagerFactory($this->security);
        $factory->load([
            'guards' => [
                'web' => [
                    'type'    => 'session',
                    'adapter' => ['name' => 'no-such-adapter', 'options' => []],
                    'extra'   => [
                        new FakeRequest(),
                        new FakeCookies(),
                        new FakeSessionManager(),
                    ],
                ],
            ],
        ]);
    }

    public function testLoadThrowsForUnknownGuard(): void
    {
        $this->expectException(\Phalcon\Auth\Exception::class);
        $this->expectExceptionMessageMatches('/Unknown auth guard/');

        $factory = new ManagerFactory($this->security);
        $factory->load([
            'guards' => [
                'web' => [
                    'type'    => 'no-such-guard',
                    'adapter' => ['name' => 'memory', 'options' => []],
                ],
            ],
        ]);
    }

    public function testLoadThrowsWhenSessionMissingExtras(): void
    {
        $this->expectException(\Phalcon\Auth\Exception::class);

        $factory = new ManagerFactory($this->security);
        $factory->load([
            'guards' => [
                'web' => [
                    'type'    => 'session',
                    'adapter' => ['name' => 'memory', 'options' => []],
                    'extra'   => [],
                ],
            ],
        ]);
    }

    public function testLoadThrowsWhenTokenGuardMissingRequest(): void
    {
        $this->expectException(\Phalcon\Auth\Exception::class);

        $factory = new ManagerFactory($this->security);
        $factory->load([
            'guards' => [
                'api' => [
                    'type'    => 'token',
                    'adapter' => ['name' => 'memory', 'options' => []],
                    'options' => [
                        'inputKey'   => 'api_token',
                        'storageKey' => 'api_token',
                    ],
                    'extra' => [],
                ],
            ],
        ]);
    }

    public function testLoadThrowsWhenStreamMissingFile(): void
    {
        $this->expectException(\Phalcon\Auth\Exception::class);

        $factory = new ManagerFactory($this->security);
        $factory->load([
            'guards' => [
                'web' => [
                    'type'    => 'session',
                    'adapter' => ['name' => 'stream', 'options' => []],
                    'extra'   => [
                        new FakeRequest(),
                        new FakeCookies(),
                        new FakeSessionManager(),
                    ],
                ],
            ],
        ]);
    }

    public function testLoadThrowsWhenModelMissingModelClass(): void
    {
        $this->expectException(\Phalcon\Auth\Exception::class);

        $factory = new ManagerFactory($this->security);
        $factory->load([
            'guards' => [
                'web' => [
                    'type'    => 'session',
                    'adapter' => ['name' => 'model', 'options' => []],
                    'extra'   => [
                        new FakeRequest(),
                        new FakeCookies(),
                        new FakeSessionManager(),
                    ],
                ],
            ],
        ]);
    }

    public function testLoadFallsBackToEmptyUsersWhenNonArrayProvided(): void
    {
        $factory = new ManagerFactory($this->security);
        $manager = $factory->load([
            'guards' => [
                'web' => [
                    'type'    => 'session',
                    'default' => true,
                    'adapter' => [
                        'name'    => 'memory',
                        'options' => ['users' => 'not-an-array'],
                    ],
                    'options' => [],
                    'extra'   => [
                        new FakeRequest(),
                        new FakeCookies(),
                        new FakeSessionManager(),
                    ],
                ],
            ],
        ]);

        $this->assertInstanceOf(Session::class, $manager->getDefaultGuard());
    }

    public function testLoadThrowsWhenTokenGuardMissingKeys(): void
    {
        $this->expectException(\Phalcon\Auth\Exception::class);

        $factory = new ManagerFactory($this->security);
        $factory->load([
            'guards' => [
                'api' => [
                    'type'    => 'token',
                    'adapter' => ['name' => 'memory', 'options' => []],
                    'options' => [],
                    'extra'   => [new FakeRequest()],
                ],
            ],
        ]);
    }
}
