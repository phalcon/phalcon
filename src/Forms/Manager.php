<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phalcon\Forms;

use Phalcon\Exception;

/**
 * Forms Manager
 */
class Manager
{
    protected $forms = [];

    /**
     * Creates a form registering it in the forms manager
     *
     * @param object entity
     */
    public function create(string $name, $entity = null): Form
    {
        $form = new Form($entity);
        $this->forms[$name] = $form;

        return $form;
    }

    /**
     * Returns a form by its name
     */
    public function get(string $name): Form
    {
        if(array_key_exists($name, $this->forms)){
            return $this->forms[$name];
        }
        else{
            throw new Exception("There is no form with name='" . name . "'");
        }
    }

    /**
     * Checks if a form is registered in the forms manager
     */
    public function has(string $name): bool
    {
        return isset($this->forms[$name]);
    }

    /**
     * Registers a form in the Forms Manager
     */
    public function set(string $name, Form $form): Manager
    {
        $this->forms[$name] = $form;

        return $this;
    }
}
