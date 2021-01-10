<?php
/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phiz\Mvc\View\Engine\Volt;

use Phiz\Mvc\View\Exception as BaseException;

/**
 * Class for exceptions thrown by Phiz\Mvc\View
 */
class Exception extends BaseException
{
    protected $statement;

    public function __construct(string $message = "", array $statement = [], 
    	int $code = 0, \Exception $previous = null)
    {
        $this->statement = $statement;

        parent::__construct($message, $code, $previous);
    }

    /**
     * Gets currently parsed statement (if any).
     */
    public function getStatement() : array
    {
        $statement = $this->statement;

        if (!is_array($statement)) {
            $statement = [];
        }

        return $statement;
    }
}
