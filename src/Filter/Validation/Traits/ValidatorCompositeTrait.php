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

namespace Phalcon\Filter\Validation\Traits;

use Phalcon\Contracts\Filter\FilterTypes;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Exception;
use Phalcon\Filter\Validation\Exceptions\NoValidatorsInComposite;

use function get_class;

/**
 * Shared validator collection state and combined validation for composite
 * validators.
 *
 * @phpstan-import-type filter_validators from FilterTypes
 */
trait ValidatorCompositeTrait
{
    /**
     * @phpstan-var filter_validators|null
     *
     * @todo Use a default [] once Zephir supports array trait defaults
     */
    protected array | null $validators = null;

    /**
     * @phpstan-return filter_validators
     */
    public function getValidators(): array
    {
        return (array)$this->validators;
    }

    /**
     * Executes the validation
     *
     * @param Validation $validation
     * @param mixed      $field
     *
     * @return bool
     * @throws Exception
     */
    public function validate(Validation $validation, mixed $field): bool
    {
        if (empty($this->getValidators())) {
            throw new NoValidatorsInComposite(get_class($this));
        }

        foreach ($this->getValidators() as $validator) {
            if ($validator->validate($validation, $field) === false) {
                return false;
            }
        }

        return true;
    }
}
