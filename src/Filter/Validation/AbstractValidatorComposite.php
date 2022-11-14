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

use function get_class;

/**
 * This is a base class for combined fields validators
 */
abstract class AbstractValidatorComposite
    extends AbstractValidator
    implements ValidatorCompositeInterface
{
    /**
     * @var array
     */
    protected array $validators = [];

    /**
     * @return array
     */
    public function getValidators(): array
    {
        return $this->validators;
    }

    /**
     * Executes the validation
     *
     * @param Validation $validation
     * @param string     $field
     *
     * @return bool
     * @throws Exception
     */
    public function validate(Validation $validation, string $field): bool
    {
        if (true === empty($this->validators)) {
            throw new Exception(
                get_class($this)
                . " does not have any validator added"
            );
        }

        foreach ($this->validators as $validator) {
            if (false === $validator->validate($validation, $field)) {
                return false;
            }
        }

        return true;
    }
}
