<?php

namespace Phalcon\Mvc;

use Phalcon\Mvc\ModelInterface;
use Phalcon\Mvc\Model\ResultsetInterface;

interface ModelFinderInterface {
	function dispatch(string $modelName, string $method, array $arguments)  :  null | array | ModelInterface;
        function findFirst(string $modelName, array $params): ?ModelInterface;
        function find(string $modelName, mixed $parameters = null) : ?ResultsetInterface;
}