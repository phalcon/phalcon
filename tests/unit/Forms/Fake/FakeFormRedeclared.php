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

namespace Phalcon\Tests\Unit\Forms\Fake;

use Phalcon\Forms\Form;

/**
 * A form that declares its entity and attributes with no type
 */
class FakeFormRedeclared extends Form
{
    protected $attributes;

    protected $entity;
}
