<?php

namespace Phalcon\Mvc;

interface ModelFinderInterface {
	public function find(string $modelName, string $method, array $arguments)  : ?ModelInterface;
}