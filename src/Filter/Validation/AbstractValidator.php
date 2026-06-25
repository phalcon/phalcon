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

namespace Phalcon\Filter\Validation;

use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Exceptions\FieldNotPrintable;
use Phalcon\Messages\Message;
use Phalcon\Support\Helper\Arr\Whitelist;

use function array_merge;
use function current;
use function get_class;
use function implode;
use function is_array;
use function is_string;

/**
 * This is a base class for validators
 */
abstract class AbstractValidator implements ValidatorInterface
{

    /**
     * @var array
     */
    protected array $options = [];
    /**
     * Message template
     *
     * @var string|null
     */
    protected string | null $template = null;

    /**
     * Message templates
     *
     * @var array
     */
    protected array $templates = [];

    /**
     * Phalcon\Filter\Validation\Validator constructor
     *
     * @param mixed[] $options {
     *                         $option string "message"
     *                         $option string "template"
     *                         $option bool   "allowEmpty"
     *                         }
     */
    public function __construct(array $options = [])
    {
        $template = current(
            (new Whitelist())($options, ["template", "message", 0])
        );

        if (is_array($template)) {
            $this->setTemplates($template);
        } elseif (is_string($template)) {
            $this->setTemplate($template);
        }

        if ($template) {
            // save custom message in options
            $options["message"] = $template;

            unset($options["template"], $options[0]);
        }

        $this->options = $options;
    }

    /**
     * Returns an option in the validator's options
     * Returns null if the option hasn't set
     *
     * @param string     $key
     * @param mixed|null $defaultValue
     *
     * @return mixed
     */
    public function getOption(string $key, mixed $defaultValue = null): mixed
    {
        if (!isset($this->options[$key])) {
            return $defaultValue;
        }

        return $this->options[$key];
    }

    /**
     * Get the template message
     *
     * @param string|null $field
     *
     * @return string
     */
    public function getTemplate(string | null $field = null): string
    {
        // there is a template in field
        if (null !== $field && isset($this->templates[$field])) {
            return $this->templates[$field];
        }

        // there is a custom template
        if (!empty($this->template)) {
            return $this->template;
        }

        // default template message
        return "The field :field is not valid for " . get_class($this);
    }

    /**
     * Get templates collection object
     *
     * @return array
     */
    public function getTemplates(): array
    {
        return $this->templates;
    }

    /**
     * Checks if an option is defined
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasOption(string $key): bool
    {
        return isset($this->options[$key]);
    }

    /**
     * Checks whether the field can be considered empty and therefore
     * skipped, honoring the `allowEmpty` option (boolean flag, list of
     * empty values, or per-field map).
     *
     * @param Validation $validation
     * @param string     $field
     *
     * @return bool
     */
    public function isAllowEmpty(Validation $validation, string $field): bool
    {
        $value = $validation->getValue($field);

        return $this->allowEmpty($field, $value);
    }

    /**
     * Create a default message by factory
     *
     * @param Validation   $validation
     * @param array|string $field
     * @param array        $replacements
     *
     * @return Message
     */
    public function messageFactory(
        Validation $validation,
        array | string $field,
        array $replacements = []
    ): Message {
        if (is_array($field)) {
            $singleField = implode(", ", $field);
        } elseif (is_string($field)) {
            $singleField = $field;
        } else {
            throw new FieldNotPrintable();
        }

        $replacements = array_merge(
            [
                ":field" => $this->prepareLabel($validation, $singleField),
            ],
            $replacements
        );

        return new Message(
            strtr($this->getTemplate($singleField), $replacements),
            $singleField,
            get_class($this),
            $this->prepareCode($singleField)
        );
    }

    /**
     * Sets an option in the validator
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function setOption(string $key, mixed $value): void
    {
        $this->options[$key] = $value;
    }

    /**
     * Set a new template message
     *
     * @param string $template
     *
     * @return ValidatorInterface
     */
    public function setTemplate(string $template): ValidatorInterface
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Clear current templates and set new from an array,
     *
     * @param array $templates
     *
     * @return ValidatorInterface
     */
    public function setTemplates(array $templates): ValidatorInterface
    {
        $this->templates = [];

        foreach ($templates as $field => $template) {
            $field                   = (string)$field;
            $template                = (string)$template;
            $this->templates[$field] = $template;
        }

        return $this;
    }

    /**
     * Executes the validation
     *
     * @param Validation $validation
     * @param string     $field
     *
     * @return bool
     */
    abstract public function validate(
        Validation $validation,
        string $field
    ): bool;

    /**
     * Checks if field can be empty.
     *
     * @param string $field
     * @param mixed  $value
     *
     * @return bool
     */
    protected function allowEmpty(mixed $field, mixed $value): bool
    {
        $allowEmpty = $this->getOption("allowEmpty", false);

        if (is_array($allowEmpty)) {
            /**
             * Per-field map: ['fieldName' => true/false]
             * Used by multi-field validators such as Ip.
             */
            if (isset($allowEmpty[$field])) {
                return $allowEmpty[$field] && empty($value);
            }

            /**
             * Value list: [null, '']
             * Strict comparison so that '0' is not treated as empty.
             */
            foreach ($allowEmpty as $emptyValue) {
                if ($emptyValue === $value) {
                    return true;
                }
            }

            return false;
        }

        return $allowEmpty && empty($value);
    }

    /**
     * Checks if a value is an array and returns the element based on the
     * passed field name
     *
     * @param mixed  $value
     * @param string $field
     *
     * @return mixed
     */
    protected function checkArray(mixed $value, string $field): mixed
    {
        if (is_array($value)) {
            $value = $value[$field] ?? $value;
        }

        return $value;
    }

    /**
     * Prepares a validation code.
     *
     * @param string $field
     *
     * @return int
     */
    protected function prepareCode(string $field): int
    {
        $code = $this->getOption("code", 0);

        if (is_array($code)) {
            $code = $code[$field];
        }

        return $code;
    }

    /**
     * Prepares a label for the field.
     *
     * @param Validation $validation
     * @param string     $field
     *
     * @return mixed
     */
    protected function prepareLabel(
        Validation $validation,
        string $field
    ): mixed {
        $label = $this->getOption("label");

        if (is_array($label)) {
            $label = $label[$field];
        }

        if (empty($label)) {
            $label = $validation->getLabel($field);
        }

        return $label;
    }
}
