<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Implementation of this file has been heavily influenced by CapsulePHP.
 * Additionally, there are implementations from ioc-interop, which is a
 * Composer dependency, and from service-interop and resolver-interop. The
 * latter two are copied and re-implemented here: service-interop is not yet
 * published on Packagist, and resolver-interop requires PHP 8.4 (this project
 * targets PHP 8.1). Once both packages become available and compatible, the
 * copies will be replaced with the actual Composer dependencies.
 *
 * @link    https://github.com/capsulephp/di
 * @license https://github.com/capsulephp/di/blob/3.x/LICENSE.md
 *
 * @link    https://github.com/ioc-interop/interface
 * @license https://github.com/ioc-interop/interface/blob/1.x/LICENSE.md
 *
 * @link    https://github.com/service-interop/interface
 * @license https://github.com/service-interop/interface/blob/1.x/LICENSE.md
 *
 * @link    https://github.com/resolver-interop/interface/tree/1.x
 * @license https://github.com/resolver-interop/interface/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Phalcon\Bucket\Service;

use Closure;
use IocInterop\Interface\IocContainer;
use Phalcon\Bucket\Definition\ServiceDefinition;
use Phalcon\Bucket\Resolver\Resolver;

interface Collection extends IocContainer
{
    // From service-interop/ServiceCollection — alias management
    public function getAlias(string $name): string;
    public function hasAlias(string $name): bool;
    public function setAlias(string $name, string $alias): void;
    public function unsetAlias(string $name): void;

    // From service-interop/ServiceCollection — definition management
    public function getDefinition(string $name): ServiceDefinition;
    public function hasDefinition(string $name): bool;
    public function newDefinition(string $name): ServiceDefinition;
    public function setDefinition(string $name, ServiceDefinition $definition): void;
    public function unsetDefinition(string $name): void;

    // From service-interop/ServiceCollection — instance management
    public function getInstance(string $name): object;
    public function hasInstance(string $name): bool;
    public function setInstance(string $name, object $instance, string $lifetime): void;
    public function unsetInstance(string $name): void;
    public function unsetInstances(string $lifetime): void;

    // Our additions — scalar parameters
    public function getParameter(string $name): mixed;
    public function hasParameter(string $name): bool;
    public function setParameter(string $name, mixed $value): void;
    public function unsetParameter(string $name): void;

    // Our additions — Bucket-specific
    public function bind(string $interface, string $concrete): ServiceDefinition;
    public function callableGet(string $name): Closure;
    public function callableNew(string $name): Closure;
    public function extend(string $name, callable $callable): void;
    public function get(string $name): mixed;
    public function getByTag(string $tag): array;
    public function getResolver(): Resolver;
    public function has(string $name): bool;
    public function isAutowireEnabled(): bool;
    public function new(string $name): mixed;
    public function set(string $name, mixed $definition): ServiceDefinition;
    public function setAutowire(bool $enabled): void;
}
