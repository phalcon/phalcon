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

namespace Phalcon\Session;

use Phalcon\Collection\Collection;
use Phalcon\Di\Di;
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\Di\Traits\InjectionAwareTrait;

use function is_array;

/**
 * Phalcon\Session\Bag
 *
 * This component helps to separate session data into "namespaces". Working by
 * this way you can easily create groups of session variables into the
 * application
 *
 * ```php
 * $user = new \Phalcon\Session\Bag("user");
 *
 * $user->name = "Kimbra Johnson";
 * $user->age  = 22;
 * ```
 * @property string           $name
 * @property ManagerInterface $session;
 */
class Bag extends Collection implements InjectionAwareInterface
{
    use InjectionAwareTrait;

    /**
     * @var string
     */
    private string $name = '';

    /**
     * @var ManagerInterface
     */
    private ManagerInterface $session;

    /**
     * Phalcon\Session\Bag constructor
     */
    public function __construct(string $name)
    {
        $this->name = $name;

        /**
         * These need to be injected
         */
        $container = Di::getDefault();

        /** @var ManagerInterface $session */
        $session         = $container->getShared('session');
        $this->container = $container;
        $this->session   = $session;

        $data = $session->get($name);
        if (true !== is_array($data)) {
            $data = [];
        }

        parent::__construct($data);
    }

    /**
     * Destroys the session bag
     */
    public function clear(): void
    {
        parent::clear();

        $this->session->remove($this->name);
    }

    /**
     * Initialize internal array
     */
    public function init(array $data = []): void
    {
        parent::init($data);
    }

    /**
     * Removes a property from the internal bag
     */
    public function remove(string $element): void
    {
        parent::remove($element);

        $this->session->set($this->name, $this->data);
    }

    /**
     * Sets a value in the session bag
     */
    public function set(string $element, $value): void
    {
        parent::set($element, $value);

        $this->session->set($this->name, $this->data);
    }
}
