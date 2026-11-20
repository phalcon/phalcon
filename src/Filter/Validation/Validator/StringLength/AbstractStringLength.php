<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Filter\Validation\Validator\StringLength;

use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\AbstractValidator;
use Phalcon\Support\Traits\WeakTypesTrait;
use Phalcon\Traits\Php\InfoTrait;

use function mb_strlen;
use function strlen;

/**
 * Shared validation for the string length validators. The validator compares
 * the length with its limit in validateLength().
 */
abstract class AbstractStringLength extends AbstractValidator
{
    use InfoTrait;
    use WeakTypesTrait;

    /**
     * Executes the validation
     */
    public function validate(Validation $validation, mixed $field): bool
    {
        /**
         * Validation iterates its validators by field name, so the field is
         * a string here. The parameter is mixed to match the untyped Zephir
         * signature.
         *
         * @var string $field
         */
        $value = self::weakCallMethod($validation, 'getValue', [$field]);
        if ($this->allowEmpty($field, $value)) {
            return true;
        }

        if ($this->rejectNonStringable($validation, $field, $value)) {
            return false;
        }

        // Check if mbstring is available to calculate the correct length
        if ($this->phpFunctionExists("mb_strlen")) {
            $length = mb_strlen((string)$value);
        } else {
            $length = strlen((string)$value);
        }

        return $this->validateLength($validation, $field, $length);
    }

    /**
     * Compares the length of the value with the limit
     *
     * @param int|string $field
     */
    abstract protected function validateLength(
        Validation $validation,
        mixed $field,
        int $length
    ): bool;
}
