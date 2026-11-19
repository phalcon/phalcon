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

namespace Phalcon\Tests\Unit\Mvc\Model\Fake;

use Phalcon\Mvc\Model;

/**
 * A model with table columns that have the names of Model properties
 */
class FakeModelRedeclared extends Model
{
    public $transaction;

    public $uniqueKey;

    public $uniqueParams;
}
