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

use Phalcon\Contracts\Forms\FormsTypes;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\CheckGroup;
use Phalcon\Forms\Element\Date;
use Phalcon\Forms\Element\ElementInterface;
use Phalcon\Forms\Element\Email;
use Phalcon\Forms\Element\File;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Radio;
use Phalcon\Forms\Element\RadioGroup;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Submit;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\TextArea;
use Phalcon\Forms\Exceptions\FormNotInLocator;
use Phalcon\Forms\Exceptions\UnknownFormElementType;

/**
 * A closure-based registry for named forms and element type factories.
 *
 * **Form registry** (`get`/`has`/`set`):
 * Each entry is a callable `fn(?object $entity): Form`. Without an entity the
 * resolved form is cached; with an entity a fresh form is always produced.
 *
 * **Element registry** (`getElement`/`hasElement`/`setElement`):
 * Maps type strings (e.g. 'text', 'email') to factories used by Form::load().
 * Each callable has the signature `fn(string $name, array $options, array $attributes): ElementInterface`.
 * Default types are seeded by `getDefaultServices()`. Users may add or override
 * types with `setElement()`.
 *
 * @phpstan-import-type forms_locator_element_factory from FormsTypes
 * @phpstan-import-type forms_locator_elements from FormsTypes
 * @phpstan-import-type forms_locator_factories from FormsTypes
 * @phpstan-import-type forms_locator_factory from FormsTypes
 */
class FormsLocator
{
    /**
     * Element type → factory callable.
     *
     * @phpstan-var forms_locator_elements
     */
    private array $elements = [];

    /**
     * Form name → factory callable.
     *
     * @var forms_locator_factories
     */
    private array $factories = [];

    /**
     * Cached entity-less form instances.
     *
     * @phpstan-var array<string, Form>
     */
    private array $instances = [];

    /**
     * @phpstan-param forms_locator_factories $definitions
     */
    public function __construct(array $definitions = [])
    {
        $this->elements = $this->getDefaultServices();

        foreach ($definitions as $name => $callable) {
            $this->set($name, $callable);
        }
    }

    /**
     * Returns the named form.
     *
     * Without an entity the result is lazily created and cached.
     * With an entity a fresh form is always produced.
     *
     * @throws Exception
     */
    public function get(string $name, object | null $entity = null): Form
    {
        if (!$this->has($name)) {
            throw new FormNotInLocator($name);
        }

        if ($entity !== null) {
            return ($this->factories[$name])($entity);
        }

        return $this->instances[$name] ??= ($this->factories[$name])(null);
    }

    // -----------------------------------------------------------------------
    // Element registry
    // -----------------------------------------------------------------------

    /**
     * Returns the factory callable for the given element type.
     *
     * @phpstan-return forms_locator_element_factory
     */
    public function getElement(string $type): callable
    {
        if (!isset($this->elements[$type])) {
            throw new UnknownFormElementType($type);
        }

        return $this->elements[$type];
    }

    /**
     * Checks whether a named form factory is registered.
     */
    public function has(string $name): bool
    {
        return isset($this->factories[$name]);
    }

    /**
     * Checks whether an element type is registered.
     */
    public function hasElement(string $type): bool
    {
        return isset($this->elements[$type]);
    }

    /**
     * Registers or replaces a named form factory.
     *
     * The callable must accept one argument (?object $entity) and return a
     * Form instance. Replacing a registration clears any cached instance so
     * the next get() call rebuilds from the new factory.
     *
     * @phpstan-param forms_locator_factory $factory
     */
    public function set(string $name, callable $factory): void
    {
        unset($this->instances[$name]);
        $this->factories[$name] = $factory;
    }

    /**
     * Registers or replaces an element type factory.
     *
     * The callable must accept (string $name, array $options, array $attributes)
     * and return an ElementInterface instance.
     *
     * @phpstan-param forms_locator_element_factory $factory
     */
    public function setElement(string $type, callable $factory): void
    {
        $this->elements[$type] = $factory;
    }

    /**
     * Returns the built-in element type factories.
     *
     * Each value is a callable: fn(string $name, array $options, array $attributes): ElementInterface
     *
     * @phpstan-return forms_locator_elements
     */
    protected function getDefaultServices(): array
    {
        return [
            'check'      => fn(string $n, array $o, array $a): ElementInterface => new Check($n, $a),
            'checkgroup' => fn(string $n, array $o, array $a): ElementInterface => new CheckGroup($n, $o, $a),
            'date'       => fn(string $n, array $o, array $a): ElementInterface => new Date($n, $a),
            'email'      => fn(string $n, array $o, array $a): ElementInterface => new Email($n, $a),
            'file'       => fn(string $n, array $o, array $a): ElementInterface => new File($n, $a),
            'hidden'     => fn(string $n, array $o, array $a): ElementInterface => new Hidden($n, $a),
            'numeric'    => fn(string $n, array $o, array $a): ElementInterface => new Numeric($n, $a),
            'password'   => fn(string $n, array $o, array $a): ElementInterface => new Password($n, $a),
            'radio'      => fn(string $n, array $o, array $a): ElementInterface => new Radio($n, $a),
            'radiogroup' => fn(string $n, array $o, array $a): ElementInterface => new RadioGroup($n, $o, $a),
            'select'     => fn(string $n, array $o, array $a): ElementInterface => new Select($n, $o, $a),
            'submit'     => fn(string $n, array $o, array $a): ElementInterface => new Submit($n, $a),
            'text'       => fn(string $n, array $o, array $a): ElementInterface => new Text($n, $a),
            'textarea'   => fn(string $n, array $o, array $a): ElementInterface => new TextArea($n, $a),
        ];
    }
}
