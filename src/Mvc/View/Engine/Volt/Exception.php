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

namespace Phalcon\Mvc\View\Engine\Volt;

use Exception as PhpException;
use Phalcon\Contracts\Mvc\MvcTypes;
use Phalcon\Mvc\View\Exception as BaseException;

/**
 * Class for exceptions thrown by Phalcon\Mvc\View
 *
 * @phpstan-import-type mvc_volt_node from MvcTypes
 */
class Exception extends BaseException
{
    /**
     * @phpstan-var mvc_volt_node
     */
    protected array $statement = [];

    /**
     * @phpstan-param mvc_volt_node $statement
     */
    public function __construct(
        string $message = "",
        array $statement = [],
        int $code = 0,
        PhpException | null $previous = null
    ) {
        $this->statement = $statement;

        parent::__construct($message, $code, $previous);
    }

    /**
     * Gets currently parsed statement (if any).
     *
     * @phpstan-return mvc_volt_node
     */
    public function getStatement(): array
    {
        return $this->statement;
    }
}
