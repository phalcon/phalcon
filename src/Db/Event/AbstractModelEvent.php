<?php

namespace Phalcon\Db\Event;

use Phalcon\Events\PsrEventInterface;
use Phalcon\Mvc\Model;

abstract class AbstractModelEvent implements PsrEventInterface
{
    public function __construct(public Model $model)
    {
    }
}
