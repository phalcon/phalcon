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

namespace Phalcon\Tests\Unit\Auth\Access;

use Phalcon\Auth\Access\Guest;
use Phalcon\Auth\Adapter\Config\MemoryAdapterConfig;
use Phalcon\Auth\Adapter\Memory;
use Phalcon\Auth\Guard\Session;
use Phalcon\Auth\Manager;
use Phalcon\Encryption\Security;
use Phalcon\Tests\AbstractUnitTestCase;
use Phalcon\Tests\Unit\Auth\Fake\FakeSessionManager;

final class GuestTest extends AbstractUnitTestCase
{
    private Memory $adapter;
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
    }

    private function buildGuard(): Session
    {
        $session = new FakeSessionManager();
        $guard   = new Session($this->adapter);
        $guard->setSession($session);

        return $guard;
    }

    public function testAllowedIfWhenNotAuthenticated(): void
    {
        $guard   = $this->buildGuard();
        $manager = new Manager();
        $manager->addGuard('web', $guard, true);

        $access = new Guest($manager);

        $this->assertTrue($access->allowedIf());
    }

    public function testAllowedIfWhenAuthenticated(): void
    {
        $guard   = $this->buildGuard();
        $manager = new Manager();
        $manager->addGuard('web', $guard, true);

        $user = $this->adapter->retrieveById(1);
        $this->assertNotNull($user);

        $guard->login($user);

        $access = new Guest($manager);

        $this->assertFalse($access->allowedIf());
    }
}
