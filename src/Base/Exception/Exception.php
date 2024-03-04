<?php
declare(strict_types=1);
namespace Phalcon\Base\Exception;

class Exception extends \Exception
{

    /**
     * Main class constructor. Which allow overriding of SPL exceptions to add custom
     * exact message within core framework.
     *
     * @param string $message
     * @param integer $code
     * @param \Exception|null $previous
     */
    public function __construct(string $message, int $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}