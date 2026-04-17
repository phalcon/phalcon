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

namespace Phalcon\Forms\Element;

use Phalcon\Forms\Exception;
use Phalcon\Html\Helper\Input\Select\ArrayData;
use Phalcon\Html\Helper\Input\Select\ResultsetData;
use Phalcon\Mvc\Model\ResultsetInterface;

use function is_array;

/**
 * Component SELECT (choice) for forms
 */
class Select extends AbstractElement
{
    /**
     * @var object|array|null
     */
    protected object | array | null $optionsValues = null;

    /**
     * Constructor
     *
     * @param string            $name
     * @param object|array|null $options
     * @param array             $attributes
     */
    public function __construct(
        string $name,
        object | array | null $options = null,
        array $attributes = []
    ) {
        $this->optionsValues = $options;

        parent::__construct($name, $attributes);
    }

    /**
     * Adds an option to the current options
     *
     * @param array|string $option
     *
     * @return ElementInterface
     */
    public function addOption(array | string $option): ElementInterface
    {
        if (is_array($option)) {
            foreach ($option as $key => $value) {
                $this->optionsValues[$key] = $value;
            }
        } else {
            $this->optionsValues[] = $option;
        }

        return $this;
    }

    /**
     * Returns the choices' options
     *
     * @return array|object
     */
    public function getOptions(): array | object
    {
        return $this->optionsValues;
    }

    /**
     * Renders the element widget returning HTML
     *
     * @param array $attributes
     *
     * @return string
     */
    public function render(array $attributes = []): string
    {
        $attrs = $this->prepareAttributes($attributes);

        $name = $attrs[0];
        unset($attrs[0]);

        $value = $attrs['value'] ?? null;
        unset($attrs['value']);

        $useEmpty   = $attrs['useEmpty'] ?? false;
        $emptyValue = $attrs['emptyValue'] ?? '';
        $emptyText  = $attrs['emptyText'] ?? 'Choose...';
        $using      = $attrs['using'] ?? null;

        unset($attrs['useEmpty'], $attrs['emptyValue'], $attrs['emptyText'], $attrs['using']);

        if (!isset($attrs['name'])) {
            $attrs['name'] = $name;
        }

        if (!str_contains($name, '[') && !isset($attrs['id'])) {
            $attrs['id'] = $name;
        }

        $select = $this->getLocalTagFactory()->newInstance('inputSelect');
        $select('', '', $attrs);

        if (null !== $value) {
            $select->selected((string) $value);
        }

        if ($useEmpty) {
            $select->addPlaceholder($emptyText, $emptyValue, [], true);
        }

        $options = $this->optionsValues;

        if (is_array($options)) {
            $select->fromData(new ArrayData($options));
        } elseif ($options instanceof ResultsetInterface) {
            if (null === $using || !is_array($using)) {
                throw Exception::usingParameterRequired();
            }

            $select->fromData(new ResultsetData($options, $using));
        }

        $html = (string) $select;

        if ('' === $html) {
            return $this->getLocalTagFactory()
                        ->newInstance('element')('select', PHP_EOL, $attrs, true);
        }

        return $html;
    }

    /**
     * Set the choice's options
     *
     * @param array|object $options
     *
     * @return ElementInterface
     */
    public function setOptions(array | object $options): ElementInterface
    {
        $this->optionsValues = $options;

        return $this;
    }

    /**
     * Returns an array of prepared attributes for Phalcon\Html\TagFactory
     * helpers according to the element parameters
     *
     * @param array $attributes
     *
     * @return array
     */
    protected function prepareAttributes(array $attributes = []): array
    {
        $name          = $this->name;
        $attributes[0] = $name;

        /**
         * Merge passed parameters with default ones
         */
        $merged = array_merge($this->attributes, $attributes);

        /**
         * Get the current element value
         */
        $value = $this->getValue();

        /**
         * If the widget has a value set it as default value
         */
        if (null !== $value) {
            $merged["value"] = $value;
        }

        return $merged;
    }
}
