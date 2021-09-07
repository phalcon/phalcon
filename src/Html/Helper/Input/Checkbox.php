<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Html\Helper\Input;

use Phalcon\Html\Escaper;

use function array_merge;

/**
 * Class Checkbox
 *
 * @package Phalcon\Html\Helper\Input
 *
 * @property array  $label
 * @property string $type
 */
class Checkbox extends AbstractInput
{
    /**
     * @var array
     */
    protected array $label = [];

    /**
     * @var string
     */
    protected string $type = 'checkbox';

    /**
     * AbstractHelper constructor.
     *
     * @param Escaper $escaper
     */
    public function __construct(Escaper $escaper)
    {
        parent::__construct($escaper);

        $this->label = [
            'start' => '',
            'text'  => '',
            'end'   => '',
        ];
    }

    /**
     * Returns the HTML for the input.
     *
     * @return string
     */
    public function __toString()
    {
        $this->processChecked();

        $output = $this->processUnchecked()
            . $this->label['start']
            . parent::__toString()
            . $this->label['text']
            . $this->label['end'];

        $this->label = [
            'start' => '',
            'text'  => '',
            'end'   => '',
        ];

        return $output;
    }

    /**
     * Attaches a label to the element
     *
     * @param array $attributes
     *
     * @return Checkbox
     */
    public function label(array $attributes = []): Checkbox
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
     * Processes the checked value
     */
    private function processChecked(): void
    {
        $checked = $this->attributes['checked'] ?? '';
        unset($this->attributes['checked']);

        if (!empty($checked)) {
            $value = $this->attributes['value'] ?? '';
            if ($checked === $value) {
                $this->attributes['checked'] = 'checked';
            }
        }
    }

    /**
     * Returns the unchecked hidden element if available
     *
     * @return string
     */
    private function processUnchecked(): string
    {
        $unchecked = $this->attributes['unchecked'] ?? '';
        unset($this->attributes['unchecked']);

        if (!empty($unchecked)) {
            $unchecked = $this->renderTag(
                'hidden',
                [
                    'name'  => $this->attributes['name'],
                    'value' => $unchecked,
                ]
            );
        }

        return $unchecked;
    }
}
