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

namespace Phalcon\Mvc\View\Engine\Volt\Exceptions;

use Phalcon\Contracts\Mvc\MvcTypes;
use Phalcon\Mvc\View\Engine\Volt\Exception;

/**
 * @phpstan-import-type mvc_volt_node from MvcTypes
 */
class CorruptedStatementWithData extends Exception
{
    /**
     * @phpstan-param mvc_volt_node $statement
     */
    public function __construct(array $statement)
    {
        parent::__construct("Corrupt statement", $statement);
    }
}
