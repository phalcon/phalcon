<?php

declare(strict_types=1);

$root = dirname(realpath(__DIR__) . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
define('PROJECT_PATH', $root);

require_once $root . 'tests/_config/functions.php';

loadIni();
loadFolders();
loadDefined();

unset($root);
