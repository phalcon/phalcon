<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Controllers;

use Phalcon\Components\Attributes\Router\Delete;
use Phalcon\Components\Attributes\Router\Route;
use Phalcon\Components\Attributes\Router\RoutePrefix;

#[RoutePrefix('/attributes')]
class AttributesController
{
    #[Route('/', methods: ['GET'])]
    public function indexAction(): void
    {
    }

    #[Delete('/{id:[0-9+]}', converters: ['id' => '\\Phalcon\\Tests\\Controllers\\AttributesController::checkId'])]
    public function deleteAction(int $id): void
    {
    }

    public static function checkId($id): int
    {
        return (int)$id;
    }
}
