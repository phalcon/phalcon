<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Implementation of this file has been influenced by AuraPHP
 * @link    https://github.com/auraphp/Aura.Html
 * @license https://github.com/auraphp/Aura.Html/blob/2.x/LICENSE
 */

declare(strict_types=1);

namespace Phalcon\Html\Helper\Input;

use Phalcon\Contracts\Html\Helper\Input\SelectData;
use Phalcon\Html\Helper\AbstractList;

use function is_array;
use function is_numeric;

/**
 * Class Select
 *
 * @property string $elementTag
 * @property bool   $inOptGroup
 * @property string $selected
 * @property bool   $strict
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
     * @var bool
     */
    protected bool $strict = false;

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
    ): static {
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
     * Add a placeholder to the element
     *
     * @param string      $text
     * @param string|null $value
     * @param array       $attributes
     * @param bool        $raw
     *
     * @return static
     */
    public function addPlaceholder(
        string $text,
        string | null $value = null,
        array $attributes = [],
        bool $raw = false
    ): static {
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
     * @param SelectData $data
     *
     * @return Select
     */
    public function fromData(SelectData $data): static
    {
        $attributes = $data->getAttributes();

        foreach ($data->getOptions() as $key => $value) {
            if (is_array($value)) {
                $this->optGroup((string) $key);

                foreach ($value as $subKey => $subValue) {
                    $subAttrs = $attributes[$subKey] ?? [];
                    $this->add((string) $subValue, (string) $subKey, $subAttrs);
                }

                $this->optGroup((string) $key);
            } else {
                $optionAttrs = $attributes[$key] ?? [];
                $this->add((string) $value, (string) $key, $optionAttrs);
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
    ): static {
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
     * Adds a non-selectable placeholder option as the first entry. Renders
     * as `<option value="" disabled selected>$text</option>`, matching the
     * common HTML idiom for "Choose..."-style prompts.
     *
     * @param string $text
     *
     * @return Select
     */
    public function placeholder(string $text): static
    {
        $this->store[] = [
            'renderFullElement',
            [
                $this->elementTag,
                $text,
                [
                    'value'    => '',
                    'disabled' => 'disabled',
                    'selected' => 'selected',
                ],
                false,
            ],
            $this->indent(),
        ];

        return $this;
    }

    /**
     * @param string $selected
     *
     * @return Select
     */
    public function selected(string $selected): static
    {
        $this->selected = $selected;

        return $this;
    }

    /**
     * Toggles strict (`===`) comparison between an option's `value` and
     * the previously stored `selected` value. Defaults to loose (`==`),
     * matching the round-tripping fix in `AbstractChecked` so mixed
     * int/string form data marks the right option as selected.
     *
     * @param bool $flag
     *
     * @return Select
     */
    public function strict(bool $flag = true): static
    {
        $this->strict = $flag;

        return $this;
    }

    /**
     * @return string
     */
    protected function getTag(): string
    {
        return 'select';
    }

    /**
     * @return string
     */
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
        if (is_numeric($value) || !empty($value)) {
            $attributes['value'] = $value;

            if ('' !== $this->selected) {
                $matched = $this->strict
                    ? $value === $this->selected
                    : $value == $this->selected;

                if ($matched) {
                    $attributes['selected'] = 'selected';
                }
            }
        }

        return $attributes;
    }
}
