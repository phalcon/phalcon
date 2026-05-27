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

use Phalcon\Html\Helper\AbstractHelper;

use function array_merge;
use function implode;
use function in_array;
use function is_array;
use function is_string;
use function rtrim;
use function str_contains;
use function str_replace;

use const PHP_EOL;

/**
 * Shared base for rendering a group of same-named inputs (checkbox or radio)
 * from an options array.
 *
 * Each option in the $options array may be either:
 *   - a scalar string label:  ['value' => 'Label text']
 *   - a rich definition:      ['value' => ['label' => 'Label text', 'disabled' => true, ...]]
 *
 * The $checked parameter is resolved by the concrete subclass:
 *   - CheckboxGroup compares against an array of selected values
 *   - RadioGroup compares against a single scalar value
 */
abstract class AbstractGroup extends AbstractHelper
{
    /**
     * @var mixed
     */
    protected mixed $checked = null;

    /**
     * @var string
     */
    protected string $name = '';

    /**
     * @var array
     */
    protected array $options = [];

    /**
     * @var array
     */
    protected array $sharedAttributes = [];

    /**
     * @var string
     */
    protected string $type = 'checkbox';

    /**
     * @param string               $name
     * @param array<string, mixed> $options
     * @param mixed                $checked
     * @param array<string, mixed> $attributes
     *
     * @return static
     */
    public function __invoke(
        string $name,
        array $options,
        mixed $checked = null,
        array $attributes = []
    ): static {
        $this->name             = $name;
        $this->options          = $options;
        $this->checked          = $checked;
        $this->sharedAttributes = $attributes;

        return $this;
    }

    /**
     * Renders the group of inputs as a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        $lines = [];

        foreach ($this->options as $value => $definition) {
            if (!is_string($definition) && !is_array($definition)) {
                continue;
            }

            /** @var string|array<string, mixed> $definition */
            $lines[] = $this->renderItem((string) $value, $definition);
        }

        $this->options          = [];
        $this->checked          = null;
        $this->sharedAttributes = [];

        return implode(PHP_EOL, $lines);
    }

    /**
     * Determines whether the given value is considered checked.
     *
     * @param string $value
     *
     * @return bool
     */
    abstract protected function isChecked(string $value): bool;

    /**
     * Renders a single input + optional label pair.
     *
     * @param string       $value
     * @param string|array<string, mixed> $definition
     *
     * @return string
     */
    protected function renderItem(string $value, string | array $definition): string
    {
        if (is_array($definition)) {
            $label     = $definition['label'] ?? null;
            $labelText  = is_string($label) ? $label : $value;
            $itemExtras = $definition;
            unset($itemExtras['label']);
        } else {
            $labelText  = $definition;
            $itemExtras = [];
        }

        $baseId = rtrim(str_replace(['[', ']'], ['_', ''], $this->name), '_') . '_' . $value;

        $inputAttrs = array_merge(
            $this->sharedAttributes,
            $itemExtras,
            [
                'id'    => $itemExtras['id'] ?? $baseId,
                'name'  => $this->name,
                'value' => $value,
            ]
        );

        if ($this->isChecked($value)) {
            $inputAttrs['checked'] = 'checked';
        } else {
            unset($inputAttrs['checked']);
        }

        $inputAttrs = array_merge(['type' => $this->type], $inputAttrs);

        $input = $this->renderTag('input', $inputAttrs);
        $label = $this->renderFullElement('label', $labelText, ['for' => $inputAttrs['id']]);

        return $input . $label;
    }
}
