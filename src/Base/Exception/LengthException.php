<?php
declare(strict_types=1);
namespace Phalcon\Base\Exception;

class LengthException extends \LengthException
{
    /**
     * Exception thrown if a length is invalid.
     *
     * @param string $message
     * @param integer $code
     * @param \LengthException|null $previous
     */
    public function __construct(string $message, int $code = 0, \LengthException $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}