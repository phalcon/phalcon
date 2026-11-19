<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

use Phalcon\CodeQuality\PhpCsFixer\ConfigFactory;

$root = dirname(__DIR__);

$config = ConfigFactory::create(
    [
        $root . '/src',
        $root . '/tests/unit',
        $root . '/tests/database',
    ],
    $root . '/tests/_output/.php-cs-fixer.cache'
);

/**
 * The trait must not have declare(strict_types=1): its helpers use the PHP
 * weak type rules, the same as cphalcon. When you give files on the command
 * line, also give --path-mode=intersection, else this list is not used.
 */
$config->getFinder()->notPath('Support/Traits/WeakTypesTrait.php');

return $config;
