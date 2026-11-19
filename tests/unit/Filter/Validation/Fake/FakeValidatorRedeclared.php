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

namespace Phalcon\Tests\Unit\Filter\Validation\Fake;

use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\AbstractValidator;

/**
 * A validator that declares its own template with no type
 */
class FakeValidatorRedeclared extends AbstractValidator
{
    protected $template = "Field :field is not valid here";

    public function validate(Validation $validation, $field): bool
    {
        $validation->appendMessage(
            $this->messageFactory($validation, $field)
        );

        return false;
    }
}
