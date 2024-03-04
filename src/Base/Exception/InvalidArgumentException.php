<?php
declare(strict_types=1);
namespace Phalcon\Base\Exception;

class InvalidArgumentException extends \InvalidArgumentException
{

    /**
     * Exception thrown if an argument is not of the expected type.
     *
     * @param string $message
     * @param integer $code
     * @param \InvalidArgumentException|null $previous
     */
    public function __construct(string $message, int $code = 0, \InvalidArgumentException $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}