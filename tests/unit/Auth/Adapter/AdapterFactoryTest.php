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

use Phalcon\Auth\Adapter\AdapterFactory;
use Phalcon\Contracts\Auth\Adapter\Adapter;
use Phalcon\Auth\Adapter\Memory;
use Phalcon\Auth\Adapter\Model;
use Phalcon\Auth\Adapter\Stream;
use Phalcon\Auth\Exception;
use Phalcon\Encryption\Security;
use Phalcon\Tests\AbstractUnitTestCase;

final class AdapterFactoryTest extends AbstractUnitTestCase
{
    private Security $security;

    protected function setUp(): void
    {
        $this->security = new Security();
    }

    public function testNewInstanceReturnsBuiltInMemory(): void
    {
        $factory = new AdapterFactory();
        $adapter = $factory->newInstance('memory', $this->security, []);

        $this->assertInstanceOf(Memory::class, $adapter);
    }

    public function testNewInstanceReturnsBuiltInModel(): void
    {
        $factory = new AdapterFactory();
        $adapter = $factory->newInstance('model', $this->security, ['model' => 'Foo']);

        $this->assertInstanceOf(Model::class, $adapter);
    }

    public function testNewInstanceReturnsBuiltInStream(): void
    {
        $factory = new AdapterFactory();
        $adapter = $factory->newInstance('stream', $this->security, ['file' => 'unused.json']);

        $this->assertInstanceOf(Stream::class, $adapter);
    }

    public function testNewInstanceReturnsAdapterInterface(): void
    {
        $factory = new AdapterFactory();

        $memory = $factory->newInstance('memory', $this->security, []);
        $this->assertInstanceOf(Adapter::class, $memory);

        $model  = $factory->newInstance('model', $this->security, ['model' => 'Foo']);
        $this->assertInstanceOf(Adapter::class, $model);

        $stream = $factory->newInstance('stream', $this->security, ['file' => 'unused.json']);
        $this->assertInstanceOf(Adapter::class, $stream);
    }

    public function testNewInstanceWithCustomMappingHonored(): void
    {
        $factory = new AdapterFactory(['custom' => Memory::class]);
        $adapter = $factory->newInstance('custom', $this->security, []);

        $this->assertInstanceOf(Memory::class, $adapter);
    }

    public function testNewInstanceUnknownThrows(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Service unknown_adapter is not registered');

        $factory = new AdapterFactory();
        $factory->newInstance('unknown_adapter', $this->security, []);
    }
}
