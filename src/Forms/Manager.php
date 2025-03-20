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

namespace Phalcon\Forms;

/**
 * Forms Manager
 */
class Manager
{
    /**
     * @var array
     */
    protected array $forms = [];

    /**
     * Creates a form registering it in the forms manager
     *
     * @param string      $name
     * @param object|null $entity
     *
     * @return Form
     */
    public function create(string $name, object | null $entity = null): Form
    {
        $form               = new Form($entity);
        $this->forms[$name] = $form;

        return $form;
    }

    /**
     * Returns a form by its name
     *
     * @param string $name
     *
     * @return Form
     * @throws Exception
     */
    public function get(string $name): Form
    {
        if (!isset($this->forms[$name])) {
            throw new Exception(
                "There is no form with name='"
                . $name
                . "'"
            );
        }

        return $this->forms[$name];
    }

    /**
     * Checks if a form is registered in the forms manager
     *
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->forms[$name]);
    }

    /**
     * Registers a form in the Forms Manager
     *
     * @param string $name
     * @param Form   $form
     *
     * @return $this
     */
    public function set(string $name, Form $form): Manager
    {
        $this->forms[$name] = $form;

        return $this;
    }
}
