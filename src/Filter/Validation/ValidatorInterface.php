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

/**
 * Interface for Phalcon\Filter\Validation\AbstractValidator
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
     * @return array
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
     * @param array $templates
     *
     * @return ValidatorInterface
     */
    public function setTemplates(array $templates): ValidatorInterface;

    /**
     * Executes the validation
     *
     * @param Validation $validation
     * @param string     $field
     *
     * @return bool
     */
    public function validate(Validation $validation, string $field): bool;
}
