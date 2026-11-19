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
use Phalcon\Messages\Messages;

/**
 * Overrides validate() with the cphalcon signature
 */
class FakeValidationOverride extends Validation
{
    public function validate($data = null, $entity = null, array $whitelist = []): bool | Messages
    {
        return parent::validate($data, $entity, $whitelist);
    }
}
