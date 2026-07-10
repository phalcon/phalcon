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

use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Exception;
use Phalcon\Filter\Validation\Exceptions\NoValidatorsInComposite;

use function count;
use function get_class;

/**
 * Shared validator collection state and combined validation for composite
 * validators.
 */
trait ValidatorCompositeTrait
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
        if (count($this->getValidators()) === 0) {
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
