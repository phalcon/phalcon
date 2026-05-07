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

namespace Phalcon\Tests\Unit\Auth\Guard;

use Phalcon\Auth\Adapter\Config\MemoryAdapterConfig;
use Phalcon\Auth\Adapter\Memory;
use Phalcon\Auth\Exception;
use Phalcon\Contracts\Auth\Guard\Guard;
use Phalcon\Auth\Guard\Session;
use Phalcon\Encryption\Security;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Tests\AbstractUnitTestCase;
use Phalcon\Tests\Unit\Auth\Fake\FakeCookies;
use Phalcon\Tests\Unit\Auth\Fake\FakeRequest;
use Phalcon\Tests\Unit\Auth\Fake\FakeSessionManager;

final class SessionTest extends AbstractUnitTestCase
{
    private Memory $adapter;
    private FakeCookies $cookies;
    private FakeRequest $request;
    private FakeSessionManager $session;
    private Security $security;

    protected function setUp(): void
    {
        $this->security = new Security();

        $this->adapter = new Memory(
            $this->security,
            new MemoryAdapterConfig([
                [
                    'id'       => 1,
                    'email'    => 'alice@example.com',
                    'password' => $this->security->hash('secret'),
                ],
            ])
        );

        $this->session = new FakeSessionManager();
        $this->cookies = new FakeCookies();
        $this->request = new FakeRequest();
    }

    private function buildGuard(): Session
    {
        $guard = new Session($this->adapter);
        $guard->setSession($this->session);
        $guard->setCookies($this->cookies);
        $guard->setRequest($this->request);

        return $guard;
    }

    public function testCheckIsFalseWhenNotAuthenticated(): void
    {
        $guard = $this->buildGuard();

        $this->assertFalse($guard->check());
        $this->assertTrue($guard->guest());
    }

    public function testAttemptSucceedsWithValidCredentials(): void
    {
        $guard = $this->buildGuard();

        $result = $guard->attempt([
            'email'    => 'alice@example.com',
            'password' => 'secret',
        ]);

        $this->assertTrue($result);
        $this->assertTrue($guard->check());
        $this->assertSame(1, $guard->id());
    }

    public function testAttemptFailsWithInvalidCredentials(): void
    {
        $guard = $this->buildGuard();

        $result = $guard->attempt([
            'email'    => 'alice@example.com',
            'password' => 'wrong-password',
        ]);

        $this->assertFalse($result);
        $this->assertFalse($guard->check());
    }

    public function testAttemptFailsWithUnknownEmail(): void
    {
        $guard = $this->buildGuard();

        $result = $guard->attempt([
            'email'    => 'nobody@example.com',
            'password' => 'secret',
        ]);

        $this->assertFalse($result);
        $this->assertFalse($guard->check());
    }

    public function testLoginPersistsToSession(): void
    {
        $guard = $this->buildGuard();
        $user  = $this->adapter->retrieveById(1);

        $this->assertNotNull($user);

        $guard->login($user);

        $this->assertTrue($this->session->has($guard->getName()));
        $this->assertSame(1, $this->session->get($guard->getName()));
    }

    public function testLogoutClearsSession(): void
    {
        $guard = $this->buildGuard();
        $user  = $this->adapter->retrieveById(1);

        $this->assertNotNull($user);

        $guard->login($user);
        $this->assertTrue($guard->check());

        $guard->logout();

        $this->assertFalse($guard->check());
        $this->assertFalse($this->session->has($guard->getName()));
    }

    public function testOnceDoesNotPersist(): void
    {
        $guard = $this->buildGuard();

        $result = $guard->once([
            'email'    => 'alice@example.com',
            'password' => 'secret',
        ]);

        $this->assertTrue($result);
        $this->assertTrue($guard->check());
        $this->assertFalse($this->session->has($guard->getName()));
    }

    public function testEventsFiredOnLogin(): void
    {
        $guard = $this->buildGuard();

        $captured = [];

        $eventsManager = new EventsManager();
        $eventsManager->attach(
            'auth:beforeLogin',
            function () use (&$captured): void {
                $captured[] = 'auth:beforeLogin';
            }
        );
        $eventsManager->attach(
            'auth:afterLogin',
            function () use (&$captured): void {
                $captured[] = 'auth:afterLogin';
            }
        );

        $guard->setEventsManager($eventsManager);

        $user = $this->adapter->retrieveById(1);

        $this->assertNotNull($user);

        $guard->login($user);

        $this->assertSame(['auth:beforeLogin', 'auth:afterLogin'], $captured);
    }

    public function testEventsFiredOnLogout(): void
    {
        $guard = $this->buildGuard();

        $captured = [];

        $eventsManager = new EventsManager();
        $eventsManager->attach(
            'auth:beforeLogout',
            function () use (&$captured): void {
                $captured[] = 'auth:beforeLogout';
            }
        );
        $eventsManager->attach(
            'auth:afterLogout',
            function () use (&$captured): void {
                $captured[] = 'auth:afterLogout';
            }
        );

        $guard->setEventsManager($eventsManager);

        $user = $this->adapter->retrieveById(1);

        $this->assertNotNull($user);

        $guard->login($user);
        $guard->logout();

        $this->assertSame(['auth:beforeLogout', 'auth:afterLogout'], $captured);
    }

    public function testLoginThrowsWithoutSessionManager(): void
    {
        $guard = new Session($this->adapter);

        $user = $this->adapter->retrieveById(1);

        $this->assertNotNull($user);

        $this->expectException(Exception::class);

        $guard->login($user);
    }

    public function testValidateDoesNotPersist(): void
    {
        $guard = $this->buildGuard();

        $result = $guard->validate([
            'email'    => 'alice@example.com',
            'password' => 'secret',
        ]);

        $this->assertTrue($result);
        $this->assertFalse($guard->check());
    }

    public function testViaRememberDefaultsFalse(): void
    {
        $guard = $this->buildGuard();

        $this->assertFalse($guard->viaRemember());
    }
}
