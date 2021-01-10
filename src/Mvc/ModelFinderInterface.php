<?php

namespace Phiz\Mvc;

use Phiz\Mvc\ModelInterface;
use Phiz\Mvc\Model\ResultsetInterface;

interface ModelFinderInterface {
	function dispatch(string $modelName, string $method, array $arguments);
        function findFirst(string $modelName, $params): ?ModelInterface;
        function find(string $modelName, $parameters = null) : ?ResultsetInterface;
}