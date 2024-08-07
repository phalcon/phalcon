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
use Phalcon\Container\Exception\NotDefined;
use Phalcon\Container\Exception\NotFound;

use function class_exists;
use function interface_exists;

class InterfaceDefinition extends AbstractDefinition
{
    /**
     * @param string $id
     *
     * @throws NotFound
     */
    public function __construct(protected string $id)
    {
        if (!interface_exists($id)) {
            throw new NotFound("Interface '{$id}' not found.");
        }
    }

    /**
     * @param string $class
     *
     * @return $this
     * @throws NotFound
     */
    public function class(string $class): static
    {
        if (!class_exists($class)) {
            throw new NotFound("Class '{$class}' not found.");
        }

        $this->class = $class;
        return $this;
    }

    /**
     * @param Container $container
     *
     * @return object
     * @throws NotDefined
     */
    protected function instantiate(Container $container): object
    {
        if ($this->factory !== null) {
            $factory = $this->resolveArgument($container, $this->factory);
            return $factory($container);
        }

        if ($this->class !== null) {
            return $container->new($this->class);
        }

        throw new NotDefined(
            "Class/factory for interface definition '{$this->id}' not set."
        );
    }
}
