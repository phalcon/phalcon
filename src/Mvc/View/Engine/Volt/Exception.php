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

use Phalcon\Mvc\View\Exception as BaseException;

/**
 * Class for exceptions thrown by Phalcon\Mvc\View
 */
class Exception extends BaseException
{
    /**
     * @var array
     */
    protected array $statement = [];

    /**
     * @param string             $message
     * @param array              $statement
     * @param int                $code
     * @param BaseException|null $previous
     */
    public function __construct(
        string $message = "",
        array $statement = [],
        int $code = 0,
        BaseException | null $previous = null
    ) {
        $this->statement = $statement;

        parent::__construct($message, $code, $previous);
    }

    /**
     * Gets currently parsed statement (if any).
     *
     * @return array
     */
    public function getStatement(): array
    {
        return $this->statement;
    }
}
