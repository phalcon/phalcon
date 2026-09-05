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
use Phalcon\Forms\Exceptions\FormNotRegistered;

/**
 * Forms Manager
 */
class Manager
{
    /**
     * @phpstan-var array<string, Form>
     */
    protected array $forms = [];
    protected FormsLocator $locator;

    /**
     * Manager constructor.
     */
    public function __construct(FormsLocator | null $locator = null)
    {
        $this->locator = $locator ?? new FormsLocator();
    }

    /**
     * Creates a form registering it in the forms manager
     */
    public function create(string $name, object | null $entity = null): Form
    {
        $form               = new Form($entity);
        $this->forms[$name] = $form;

        return $form;
    }

    /**
     * Returns a form by its name
     */
    public function get(string $name): Form
    {
        if (!isset($this->forms[$name])) {
            throw new FormNotRegistered($name);
        }

        return $this->forms[$name];
    }

    /**
     * Returns the FormsLocator instance.
     */
    public function getLocator(): FormsLocator
    {
        return $this->locator;
    }

    /**
     * Checks if a form is registered in the forms manager
     */
    public function has(string $name): bool
    {
        return isset($this->forms[$name]);
    }

    /**
     * Creates a form from a Schema source, registers it in the manager,
     * and registers a factory in the locator for entity-aware retrieval.
     *
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
     */
    public function set(string $name, Form $form): static
    {
        $this->forms[$name] = $form;

        return $this;
    }
}
