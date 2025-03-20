<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Implementation of this file has been influenced by CapsulePHP
 *
 * @link    https://github.com/capsulephp/di
 * @license https://github.com/capsulephp/di/blob/3.x/LICENSE.md
 */

declare(strict_types=1);

namespace Phalcon\Container\Definitions;

use Phalcon\Container\Container;
use Phalcon\Container\Exception\NotInstantiated;
use Phalcon\Container\Lazy\AbstractLazy;
use Throwable;

abstract class AbstractDefinition extends AbstractLazy
{
    /**
     * @var string|null
     */
    protected string | null $class = null;
    /**
     * @var mixed|null
     */
    protected mixed /* callable */
        $factory = null;
    /**
     * @var string
     */
    protected string $id;
    /**
     * @var bool
     */
    protected bool $isInstantiable = false;

    /**
     * @param Container $container
     *
     * @return mixed
     * @throws NotInstantiated
     */
    public function __invoke(Container $container): mixed
    {
        return $this->new($container);
    }

    /**
     * @param callable $factory
     *
     * @return $this
     */
    public function factory(callable $factory): static
    {
        $this->factory = $factory;

        return $this;
    }

    /**
     * @param Container $container
     *
     * @return bool
     */
    public function isInstantiable(Container $container): bool
    {
        if ($this->factory !== null) {
            return true;
        }

        if ($this->class !== null) {
            return $container->has($this->class);
        }

        return $this->isInstantiable;
    }

    /**
     * @param Container $container
     *
     * @return object
     * @throws NotInstantiated
     */
    public function new(Container $container): object
    {
        try {
            return $this->instantiate($container);
        } catch (Throwable $ex) {
            throw new NotInstantiated(
                'Could not instantiate ' . $this->id,
                previous: $ex
            );
        }
    }

    /**
     * @param Container $container
     *
     * @return object
     */
    abstract protected function instantiate(Container $container): object;
}
