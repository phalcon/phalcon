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

namespace Phalcon\Tests\Unit\Html\Helper\Breadcrumbs;

use Phalcon\Html\Escaper;
use Phalcon\Html\Helper\Breadcrumbs;
use Phalcon\Tests\AbstractUnitTestCase;

final class AddClearRemoveToArrayTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Html\Breadcrumbs :: add()/clear()/remove()/toArray()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testHtmlHelperBreadcrumbsAddClearRemoveToArray(): void
    {
        $escaper     = new Escaper();
        $breadcrumbs = new Breadcrumbs($escaper);

        /**
         * add()/toArray()
         */
        $breadcrumbs->add('Home', '/');

        $expected = [
            '/' => [
                'text'       => 'Home',
                'icon'       => '',
                'attributes' => [],
            ],
        ];
        $actual   = $breadcrumbs->toArray();
        $this->assertSame($expected, $actual);

        $breadcrumbs->add(
            'Invoices',
            '/invoices',
            '<i class="fa-solid fa-file-invoice"></i>'
        );

        $expected = [
            '/'     => [
                'text'       => 'Home',
                'icon'       => '',
                'attributes' => [],
            ],
            '/invoices' => [
                'text'       => 'Invoices',
                'icon'       => '<i class="fa-solid fa-file-invoice"></i>',
                'attributes' => [],
            ],
        ];
        $actual   = $breadcrumbs->toArray();
        $this->assertSame($expected, $actual);

        $breadcrumbs->add(
            'Customers',
            '/customers',
            '<i class="fa-solid fa-user"></i>',
            [
                'class'      => 'breadcrumb-item',
                'aria-label' => 'breadcrumb',
            ]
        );

        $expected = [
            '/'           => [
                'text'       => 'Home',
                'icon'       => '',
                'attributes' => [],
            ],
            '/invoices'       => [
                'text'       => 'Invoices',
                'icon'       => '<i class="fa-solid fa-file-invoice"></i>',
                'attributes' => [],
            ],
            '/customers' => [
                'text'       => 'Customers',
                'icon'       => '<i class="fa-solid fa-user"></i>',
                'attributes' => [
                    'class'      => 'breadcrumb-item',
                    'aria-label' => 'breadcrumb',
                ],
            ],
        ];
        $actual   = $breadcrumbs->toArray();
        $this->assertSame($expected, $actual);

        /**
         * remove()
         */
        $breadcrumbs->remove('/invoices');
        $expected = [
            '/'           => [
                'text'       => 'Home',
                'icon'       => '',
                'attributes' => [],
            ],
            '/customers' => [
                'text'       => 'Customers',
                'icon'       => '<i class="fa-solid fa-user"></i>',
                'attributes' => [
                    'class'      => 'breadcrumb-item',
                    'aria-label' => 'breadcrumb',
                ],
            ],
        ];
        $actual   = $breadcrumbs->toArray();
        $this->assertSame($expected, $actual);

        /**
         * clear()
         */
        $breadcrumbs->clear();

        $expected = [];
        $actual   = $breadcrumbs->toArray();
        $this->assertSame($expected, $actual);
    }
}
