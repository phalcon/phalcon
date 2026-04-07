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

namespace Phalcon\Http\Message;

use Phalcon\Http\Message\Interfaces\ResponseStatusCodeInterface as StatusCodeInterface;

/**
 * Backward-compatible interface so that Phalcon\Http\Message\ResponseStatusCodeInterface
 * resolves to the same set of constants as the canonical
 * Phalcon\Http\Message\Interfaces\ResponseStatusCodeInterface.
 */
interface ResponseStatusCodeInterface extends StatusCodeInterface
{
}