<?php

namespace Phalcon\Mvc;

interface ModelFinderInterface {
	function dispatch(string $modelName, string $method, array $arguments)  :  null | array | ModelInterface;
        function findFirst(string $modelName, array $params): ?ModelInterface;
        function find(string $modelName, mixed $parameters = null) : ?ResultsetInterface;       
}