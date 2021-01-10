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

namespace Phiz\Events\Traits;

use Phiz\Events\Exception;

/**
 * Trait ManagerHelperTrait
 *
 * @package Phiz\Events\Traits
 *
 * @property bool  $collect
 * @property bool  $enablePriorities
 * @property array $events
 * @property array $responses
 */
trait ManagerHelperTrait
{
    /**
     * @var bool
     */
    protected bool $collect = false;

    /**
     * @var bool
     */
    protected bool $enablePriorities = false;

    /**
     * @var array
     */
    protected ?array $events = [];

    /**
     * @var array
     */
    protected ?array $responses = [];

    /**
     * @param mixed $handler
     *
     * @return bool
     */
    public function isValidHandler($handler): bool
    {
        if (true !== is_object($handler) && true !== is_callable($handler)) {
            return false;
        }

        return true;
    }

    /**
     * @param mixed $handler
     *
     * @throws Exception
     */
    private function checkHandler($handler): void
    {
        if (false === $this->isValidHandler($handler)) {
            throw new Exception('Event handler must be an Object or Callable');
        }
    }

    /**
     * @param string|null $type
     */
    private function processDetachAllNullType(?string $type): void
    {
        if (null === $type) {
            $this->events = [];
        }
    }

    /**
     * @param string|null $type
     */
    private function processDetachAllNotNullType(?string $type): void
    {
        if (null !== $type && true === isset($this->events[$type])) {
            unset($this->events[$type]);
        }
    }
}
