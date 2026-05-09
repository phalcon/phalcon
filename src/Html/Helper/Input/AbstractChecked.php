<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Html\Helper\Input;

use Phalcon\Html\Escaper\EscaperInterface;
use Phalcon\Html\Helper\Doctype;

use function array_key_exists;
use function array_merge;
use function is_string;
use function strtolower;

/**
 * Shared base for inputs that can be checked: `<input type="checkbox">` and
 * `<input type="radio">`. Holds the optional surrounding `<label>` markup,
 * the `unchecked` companion hidden input, and the rule that decides whether
 * the rendered tag carries `checked="checked"`.
 *
 * The match between `checked` and `value` is loose (`==`) by default so that
 * mixed int/string form input round-trips correctly (e.g. `value=0` against
 * `checked="0"`). Strict (`===`) matching is available via `strict(true)`.
 */
abstract class AbstractChecked extends AbstractInput
{
    /**
     * @var array
     */
    protected array $label = [];

    /**
     * @var bool
     */
    protected bool $strict = false;

    /**
     * @param EscaperInterface $escaper
     * @param Doctype|null     $doctype
     */
    public function __construct(
        EscaperInterface $escaper,
        ?Doctype $doctype = null
    ) {
        parent::__construct($escaper, $doctype);

        $this->label = [
            'start' => '',
            'text'  => '',
            'end'   => '',
        ];
    }

    /**
     * Returns the HTML for the input, optionally surrounded by the label
     * fragment configured via `label()` and preceded by the hidden companion
     * input emitted when an `unchecked` attribute is supplied.
     *
     * @return string
     */
    public function __toString()
    {
        $this->processChecked();

        $unchecked   = $this->processUnchecked();
        $element     = parent::__toString();
        $label       = $this->label;
        $this->label = [
            'start' => '',
            'text'  => '',
            'end'   => '',
        ];

        return $unchecked
            . $label['start']
            . $element
            . $label['text']
            . $label['end'];
    }

    /**
     * Attaches a wrapping `<label>` to the element. The supplied attributes
     * are merged with a default `for` pointing at the input's `id`. A `text`
     * pseudo-attribute, if present, becomes the label text and is stripped
     * from the rendered attributes.
     *
     * @param array $attributes
     *
     * @return static
     */
    public function label(array $attributes = []): static
    {
        $text = $attributes['text'] ?? '';
        unset($attributes['text']);

        $attributes = array_merge(
            [
                'for' => $this->attributes['id'],
            ],
            $attributes
        );

        $this->label = [
            'start' => $this->renderTag('label', $attributes),
            'text'  => $text,
            'end'   => '</label>',
        ];

        return $this;
    }

    /**
     * Toggles strict (`===`) comparison between the `checked` attribute and
     * the `value` attribute when deciding whether to render the input as
     * checked. Defaults to loose (`==`), which matches typical form-input
     * round-tripping where types may differ between the source data and the
     * value rendered into the markup.
     *
     * @param bool $flag
     *
     * @return static
     */
    public function strict(bool $flag = true): static
    {
        $this->strict = $flag;

        return $this;
    }

    /**
     * Decides whether the rendered tag carries `checked="checked"`. Two
     * paths qualify as checked: an unconditional opt-in via
     * `["checked" => "checked"]` (case-insensitive) or `["checked" => true]`,
     * and a value-match path where the supplied `checked` attribute equals
     * the input's `value` (`==` by default, `===` under `strict(true)`).
     */
    protected function processChecked(): void
    {
        $attributes = $this->attributes;

        if (!array_key_exists('checked', $attributes)) {
            return;
        }

        $checked = $attributes['checked'];
        unset($attributes['checked']);

        $matched = false;

        if ($checked === true) {
            $matched = true;
        } elseif (is_string($checked) && strtolower($checked) === 'checked') {
            $matched = true;
        } else {
            $value = $attributes['value'] ?? null;

            if ($this->strict) {
                $matched = $checked === $value;
            } else {
                $matched = $checked == $value;
            }
        }

        if ($matched) {
            $attributes['checked'] = 'checked';
        }

        $this->attributes = $attributes;
    }

    /**
     * Returns the markup for the optional hidden companion input that lets
     * a checkbox/radio submit a value when unchecked.
     *
     * @return string
     */
    protected function processUnchecked(): string
    {
        $attributes = $this->attributes;
        $unchecked  = $attributes['unchecked'] ?? '';
        unset($attributes['unchecked']);

        $this->attributes = $attributes;

        if (!empty($unchecked)) {
            return $this->renderTag(
                'hidden',
                [
                    'name'  => $this->attributes['name'],
                    'value' => $unchecked,
                ]
            );
        }

        return '';
    }
}
