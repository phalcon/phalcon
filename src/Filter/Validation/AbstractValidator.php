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

use Phalcon\Contracts\Filter\FilterTypes;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Exceptions\FieldNotPrintable;
use Phalcon\Messages\Message;
use Phalcon\Support\Helper\Arr\Whitelist;
use Stringable;

use function array_merge;
use function current;
use function get_class;
use function implode;
use function is_array;
use function is_object;
use function is_string;
use function method_exists;

/**
 * This is a base class for validators
 *
 * @phpstan-import-type filter_validator_options from FilterTypes
 * @phpstan-import-type filter_validator_replacements from FilterTypes
 * @phpstan-import-type filter_validator_templates from FilterTypes
 */
abstract class AbstractValidator implements ValidatorInterface
{
    /**
     * @phpstan-var filter_validator_options
     */
    protected array $options = [];
    /**
     * Message template
     */
    protected string | null $template = null;

    /**
     * Whether the template/message has been explicitly assigned on the
     * instance (constructor `message`/`template` option or setTemplate()).
     * While false, `template` still holds the validator's class default and a
     * global default registered via Validation::setDefaultMessages() applies.
     */
    protected bool $templateChanged = false;

    /**
     * Message templates
     *
     * @phpstan-var filter_validator_templates
     */
    protected array $templates = [];

    /**
     * Phalcon\Filter\Validation\Validator constructor
     *
     * @phpstan-param filter_validator_options $options
     */
    public function __construct(array $options = [])
    {
        $template = current(
            (new Whitelist())($options, ["template", "message", 0])
        );

        if (is_array($template)) {
            /** @phpstan-var filter_validator_templates $template */
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
     */
    public function getTemplate(string | null $field = null): string
    {
        // there is a template in field
        if (null !== $field && isset($this->templates[$field])) {
            return $this->templates[$field];
        }

        // an explicitly assigned template/message wins over a global default
        if (true === $this->templateChanged && !empty($this->template)) {
            return $this->template;
        }

        // a global default message registered for this validator class
        $defaultMessage = Validation::getDefaultMessage(get_class($this));

        if ("" !== $defaultMessage) {
            return $defaultMessage;
        }

        // custom or class default template
        if (!empty($this->template)) {
            return $this->template;
        }

        // default template message
        return "The field :field is not valid for " . get_class($this);
    }

    /**
     * Get templates collection object
     *
     * @phpstan-return filter_validator_templates
     */
    public function getTemplates(): array
    {
        return $this->templates;
    }

    /**
     * Checks if an option is defined
     */
    public function hasOption(string $key): bool
    {
        return isset($this->options[$key]);
    }

    /**
     * Checks whether the field can be considered empty and therefore
     * skipped, honoring the `allowEmpty` option (boolean flag, list of
     * empty values, or per-field map).
     */
    public function isAllowEmpty(Validation $validation, string $field): bool
    {
        $value = $validation->getValue($field);

        return $this->allowEmpty($field, $value);
    }

    /**
     * Create a default message by factory
     *
     * @param array|string $field
     *
     * @phpstan-param mixed                         $field
     * @phpstan-param filter_validator_replacements $replacements
     */
    public function messageFactory(
        Validation $validation,
        mixed $field,
        array $replacements = []
    ): Message {
        if (is_array($field)) {
            /** @phpstan-var array<array-key, string> $field */
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
     */
    public function setOption(string $key, mixed $value): void
    {
        $this->options[$key] = $value;
    }

    /**
     * Set a new template message
     */
    public function setTemplate(string $template): ValidatorInterface
    {
        $this->template        = $template;
        $this->templateChanged = true;

        return $this;
    }

    /**
     * Clear current templates and set new from an array,
     *
     * @phpstan-param filter_validator_templates $templates
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
     */
    abstract public function validate(
        Validation $validation,
        mixed $field
    ): bool;

    /**
     * Checks if field can be empty.
     */
    protected function allowEmpty(mixed $field, mixed $value): bool
    {
        $allowEmpty = $this->getOption("allowEmpty", false);

        if (is_array($allowEmpty)) {
            /**
             * Per-field map: ['fieldName' => true/false]
             * Used by multi-field validators such as Ip.
             *
             * @var string $field
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
     */
    protected function prepareCode(string $field): int
    {
        /** @phpstan-var array<string, int>|int $code */
        $code = $this->getOption("code", 0);

        if (is_array($code)) {
            $code = $code[$field];
        }

        return $code;
    }

    /**
     * Prepares a label for the field.
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

    /**
     * Rejects a value that cannot be a string: an array, or an object without
     * __toString(). A cast would turn an array into the constant "Array",
     * which satisfies the string checks. Appends the message and returns
     * true when the value is rejected.
     *
     * @phpstan-assert-if-false string|int|float|bool|Stringable|null $value
     */
    protected function rejectNonStringable(
        Validation $validation,
        mixed $field,
        mixed $value
    ): bool {
        if (is_array($value) || (is_object($value) && !method_exists($value, '__toString'))) {
            $validation->appendMessage(
                $this->messageFactory($validation, $field)
            );

            return true;
        }

        return false;
    }
}
