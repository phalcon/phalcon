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

use Phalcon\Contracts\Forms\Schema;

/**
 * Forms Manager
 */
class Manager
{
    /**
     * @var array<string, Form>
     */
    protected array $forms = [];

    /**
     * @var FormsLocator
     */
    private FormsLocator $locator;

    /**
     * @param FormsLocator|null $locator
     */
    public function __construct(FormsLocator | null $locator = null)
    {
        $this->locator = $locator ?? new FormsLocator();
    }

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
     * Returns the FormsLocator instance.
     *
     * @return FormsLocator
     */
    public function getLocator(): FormsLocator
    {
        return $this->locator;
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
     * Creates a form from a Schema source, registers it in the manager,
     * and registers a factory in the locator for entity-aware retrieval.
     *
     * @param string      $name
     * @param Schema      $schema
     * @param object|null $entity
     *
     * @return Form
     * @throws Exception
     */
    public function loadForm(
        string $name,
        Schema $schema,
        object | null $entity = null
    ): Form {
        $locator            = $this->locator;
        $form               = (new Form($entity))->load($schema, $locator);
        $this->forms[$name] = $form;

        $this->locator->set(
            $name,
            fn(object | null $e) => (new Form($e))->load($schema, $locator)
        );

        return $form;
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
