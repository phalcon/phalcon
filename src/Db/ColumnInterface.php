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

namespace Phalcon\Db;

use Phalcon\Contracts\Db\Column as ColumnContract;

/**
 * Phalcon\Db\ColumnInterface
 *
 * @deprecated Will be removed in a future major release.
 *             Use {@see \Phalcon\Contracts\Db\Column} instead.
 */
interface ColumnInterface extends ColumnContract
{
}
