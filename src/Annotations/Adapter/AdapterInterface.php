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

namespace Phalcon\Annotations\Adapter;

use Phalcon\Storage\Adapter\AdapterInterface as StorageAdapterInterface;

/**
 * This interface must be implemented by adapters in Phalcon\Components\Attributes
 */
interface AdapterInterface extends StorageAdapterInterface
{
}
