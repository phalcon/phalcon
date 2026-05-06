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

namespace Phalcon\Tests\Unit\Auth\Adapter;

use Phalcon\Auth\Adapter\Config\StreamAdapterConfig;
use Phalcon\Auth\Adapter\Stream;
use Phalcon\Contracts\Auth\AuthUser;
use Phalcon\Auth\Exception;
use Phalcon\Encryption\Security;
use Phalcon\Tests\AbstractUnitTestCase;
use Phalcon\Tests\Unit\Auth\Fake\FakeStreamAdapter;

final class StreamTest extends AbstractUnitTestCase
{
    private Security $security;
    private string $hashedPassword;

    protected function setUp(): void
    {
        $this->security       = new Security();
        $this->hashedPassword = $this->security->hash('mypassword');
    }

    public function testRetrieveByCredentialsMatchesUserFromInjectedArray(): void
    {
        $adapter = new FakeStreamAdapter($this->security);
        $adapter->setUsers(
            [
                [
                    'id'       => 1,
                    'email'    => 'carol@example.com',
                    'password' => $this->hashedPassword,
                ],
            ]
        );

        $user = $adapter->retrieveByCredentials(['email' => 'carol@example.com']);

        $this->assertInstanceOf(AuthUser::class, $user);
        $this->assertSame(1, $user->getAuthIdentifier());
    }

    public function testRetrieveByCredentialsReturnsNullOnMiss(): void
    {
        $adapter = new FakeStreamAdapter($this->security);
        $adapter->setUsers(
            [
                [
                    'id'       => 1,
                    'email'    => 'carol@example.com',
                    'password' => $this->hashedPassword,
                ],
            ]
        );

        $user = $adapter->retrieveByCredentials(['email' => 'nobody@example.com']);

        $this->assertNull($user);
    }

    public function testRetrieveByIdReturnsUserFromInjectedArray(): void
    {
        $adapter = new FakeStreamAdapter($this->security);
        $adapter->setUsers(
            [
                [
                    'id'       => 7,
                    'email'    => 'dave@example.com',
                    'password' => $this->hashedPassword,
                ],
            ]
        );

        $user = $adapter->retrieveById(7);

        $this->assertInstanceOf(AuthUser::class, $user);
        $this->assertSame(7, $user->getAuthIdentifier());
    }

    public function testValidateCredentialsAcceptsCorrectPassword(): void
    {
        $adapter = new FakeStreamAdapter($this->security);
        $adapter->setUsers(
            [
                [
                    'id'       => 3,
                    'email'    => 'eve@example.com',
                    'password' => $this->hashedPassword,
                ],
            ]
        );

        $user = $adapter->retrieveById(3);

        $this->assertNotNull($user);

        $result = $adapter->validateCredentials($user, ['password' => 'mypassword']);

        $this->assertTrue($result);
    }

    public function testValidateCredentialsRejectsWrongPassword(): void
    {
        $adapter = new FakeStreamAdapter($this->security);
        $adapter->setUsers(
            [
                [
                    'id'       => 3,
                    'email'    => 'eve@example.com',
                    'password' => $this->hashedPassword,
                ],
            ]
        );

        $user = $adapter->retrieveById(3);

        $this->assertNotNull($user);

        $result = $adapter->validateCredentials($user, ['password' => 'wrongpassword']);

        $this->assertFalse($result);
    }

    public function testConfigThrowsWhenFileEmpty(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/file/');

        new StreamAdapterConfig('');
    }

    public function testThrowsWhenFileDoesNotExist(): void
    {
        $this->expectException(Exception::class);

        $adapter = new Stream(
            $this->security,
            new StreamAdapterConfig(__DIR__ . '/this-does-not-exist.json')
        );
        $adapter->retrieveById(1);
    }

    public function testThrowsWhenFileNotJsonArray(): void
    {
        $this->expectException(Exception::class);

        $notArrayJson = dirname(__DIR__, 3) . '/support/assets/auth/not-array.json';

        $adapter = new Stream(
            $this->security,
            new StreamAdapterConfig($notArrayJson)
        );
        $adapter->retrieveById(1);
    }
}
