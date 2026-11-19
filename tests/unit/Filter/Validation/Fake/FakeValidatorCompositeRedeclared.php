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

use Phalcon\Filter\Validation\AbstractValidatorComposite;

/**
 * A composite validator that declares its validators with no type
 */
class FakeValidatorCompositeRedeclared extends AbstractValidatorComposite
{
    protected $validators = [];
}
