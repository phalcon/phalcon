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

namespace Phalcon\Cache\Exception;

use Psr\SimpleCache\InvalidArgumentException as PsrInvalidArgumentException;

/**
 * Exceptions thrown in Phalcon\Cache for invalid arguments will use this class
 */
class InvalidArgumentException extends \Exception implements PsrInvalidArgumentException
{
}
