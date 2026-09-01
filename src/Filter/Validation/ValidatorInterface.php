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

/**
 * Interface for Phalcon\Filter\Validation\AbstractValidator
 *
 * @phpstan-import-type filter_validator_templates from FilterTypes
 */
interface ValidatorInterface
{
    /**
     * Returns an option in the validator's options
     * Returns null if the option hasn't set
     *
     * @param string     $key
     * @param mixed|null $defaultValue
     *
     * @return mixed
     */
    public function getOption(string $key, mixed $defaultValue = null): mixed;

    /**
     * Get the template message
     *
     * @param string $field
     *
     * @return string
     */
    public function getTemplate(string $field): string;

    /**
     * Get message templates
     *
     * @phpstan-return filter_validator_templates
     */
    public function getTemplates(): array;

    /**
     * Checks if an option is defined
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasOption(string $key): bool;

    /**
     * Set a new template message
     *
     * @param string $template
     *
     * @return ValidatorInterface
     */
    public function setTemplate(string $template): ValidatorInterface;

    /**
     * Clear current template and set new from an array,
     *
     * @phpstan-param filter_validator_templates $templates
     *
     * @return ValidatorInterface
     */
    public function setTemplates(array $templates): ValidatorInterface;

    /**
     * Executes the validation
     *
     * @param Validation $validation
     * @param mixed      $field
     *
     * @return bool
     */
    public function validate(Validation $validation, mixed $field): bool;
}
