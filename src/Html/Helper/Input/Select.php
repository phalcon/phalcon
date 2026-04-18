<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Html\Helper\Input;

use Phalcon\Html\Helper\AbstractList;
use Phalcon\Html\Helper\Input\Select\SelectDataInterface;

use function is_array;

/**
 * Class Select
 *
 * @property string $elementTag
 * @property bool   $inOptGroup
 * @property string $selected
 */
class Select extends AbstractList
{
    /**
     * @var string
     */
    protected string $elementTag = 'option';

    /**
     * @var bool
     */
    protected bool $inOptGroup = false;

    /**
     * @var string
     */
    protected string $selected = '';

    /**
     * Add an element to the list
     *
     * @param string      $text
     * @param string|null $value
     * @param array       $attributes
     * @param bool        $raw
     *
     * @return Select
     */
    public function add(
        string $text,
        string | null $value = null,
        array $attributes = [],
        bool $raw = false
    ): Select {
        $attributes = $this->processValue($attributes, $value);

        $this->store[] = [
            'renderFullElement',
            [
                $this->elementTag,
                $text,
                $attributes,
                $raw,
            ],
            $this->indent(),
        ];

        return $this;
    }

    /**
     * Add an element to the list
     *
     * @param string      $text
     * @param string|null $value
     * @param array       $attributes
     * @param bool        $raw
     *
     * @return Select
     */
    public function addPlaceholder(
        string $text,
        string | null $value = null,
        array $attributes = [],
        bool $raw = false
    ): Select {
        if (null !== $value) {
            $attributes['value'] = $value;
        }

        $this->store[] = [
            'renderFullElement',
            [
                $this->elementTag,
                $text,
                $attributes,
                $raw,
            ],
            $this->indent(),
        ];

        return $this;
    }

    /**
     * Populates the select from a data provider.
     *
     * Flat entries: key = option value, value = label string.
     * Optgroup entries: key = group label, value = [value => label] array.
     *
     * @param SelectDataInterface $data
     *
     * @return Select
     */
    public function fromData(SelectDataInterface $data): Select
    {
        foreach ($data->getOptions() as $key => $value) {
            if (is_array($value)) {
                $this->optGroup((string) $key);
                foreach ($value as $subKey => $subValue) {
                    $this->add((string) $subValue, (string) $subKey);
                }
                $this->optGroup((string) $key);
            } else {
                $this->add((string) $value, (string) $key);
            }
        }

        return $this;
    }

    /**
     * Creates an option group
     *
     * @param string|null $label
     * @param array       $attributes
     *
     * @return Select
     */
    public function optGroup(
        string | null $label = null,
        array $attributes = []
    ): Select {
        if (!$this->inOptGroup) {
            $this->store[]     = [
                'optGroupStart',
                [
                    $label,
                    $attributes,
                ],
                $this->indent(),
            ];
            $this->indentLevel += 1;
        } else {
            $this->indentLevel -= 1;
            $this->store[]     = [
                'optGroupEnd',
                [],
                $this->indent(),
            ];
        }

        $this->inOptGroup = !$this->inOptGroup;

        return $this;
    }

    /**
     * @param string $selected
     *
     * @return Select
     */
    public function selected(string $selected): Select
    {
        $this->selected = $selected;

        return $this;
    }

    /**
     * @return string
     */
    protected function getTag(): string
    {
        return 'select';
    }

    protected function optGroupEnd(): string
    {
        return '</optgroup>';
    }

    /**
     * @param string $label
     * @param array  $attributes
     *
     * @return string
     */
    protected function optGroupStart(string $label, array $attributes): string
    {
        $attributes['label'] = $label;

        return $this->renderTag('optgroup', $attributes);
    }

    /**
     * Checks if the value has been passed and if it is the same as the
     * value stored in the object
     *
     * @param array       $attributes
     * @param string|null $value
     *
     * @return array
     */
    private function processValue(
        array $attributes,
        string | null $value = null
    ): array {
        if (null !== $value) {
            $attributes['value'] = $value;
            if (!empty($this->selected) && $value === $this->selected) {
                $attributes['selected'] = 'selected';
            }
        }

        return $attributes;
    }
}
