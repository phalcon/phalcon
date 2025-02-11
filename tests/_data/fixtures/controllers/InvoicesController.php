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

use Phalcon\Annotations\Router\Get;
use Phalcon\Annotations\Router\Route;
use Phalcon\Annotations\Router\RoutePrefix;

#[RoutePrefix("/invoices")]
class InvoicesController
{
    #[Get("/")]
    public function indexAction()
    {
    }

    #[Get("/edit/{id:[0-9]+}", name: "edit-invoice")]
    public function editAction($id)
    {
    }

    #[Route("/save", methods: ["POST", "PUT"], name: "save-invoice")]
    public function saveAction()
    {
    }
}
