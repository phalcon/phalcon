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

use Phalcon\Contracts\Forms\FormsTypes;
use Phalcon\Contracts\Html\HtmlTypes;
use Phalcon\Html\Helper\Input\RadioGroup as RadioGroupHelper;

/**
 * Component for a group of INPUT[type=radio] elements.
 *
 * Options are passed as an associative array:
 *   ['value' => 'Label']
 * or with per-item attributes:
 *   ['value' => ['label' => 'Label', 'disabled' => true]]
 *
 * @phpstan-import-type forms_attributes from FormsTypes
 * @phpstan-import-type forms_group_options from FormsTypes
 * @phpstan-import-type html_attributes from HtmlTypes
 */
class RadioGroup extends AbstractElement
{
    /**
     * @var forms_group_options
     */
    protected array $optionsValues = [];

    /**
     * Constructor
     *
     * @phpstan-param forms_group_options $options
     * @phpstan-param forms_attributes $attributes
     */
    public function __construct(
        string $name,
        array $options = [],
        array $attributes = []
    ) {
        $this->optionsValues = $options;

        parent::__construct($name, $attributes);
    }

    /**
     * Returns the group options
     *
     * @phpstan-return forms_group_options
     */
    public function getOptions(): array
    {
        return $this->optionsValues;
    }

    /**
     * Renders the radio group returning HTML
     *
     * @phpstan-param html_attributes $attributes
     */
    public function render(array $attributes = []): string
    {
        $value  = $this->getValue();
        /** @var html_attributes $merged */
        $merged = array_merge($this->attributes, $attributes);
        /** @var RadioGroupHelper $helper */
        $helper = $this->getLocalTagFactory()->newInstance('inputRadioGroup');

        return (string) $helper($this->name, $this->optionsValues, $value, $merged);
    }

    /**
     * Sets the group options
     *
     * @phpstan-param forms_group_options $options
     */
    public function setOptions(array $options): ElementInterface
    {
        $this->optionsValues = $options;

        return $this;
    }
}
