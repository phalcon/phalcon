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
            '75192c92acf8997b8863087bb3428b3b250c4e9b' => [
                'attributes' => [],
                'icon'       => '',
                'link'       => '/',
                'text'       => 'Home',
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
            '75192c92acf8997b8863087bb3428b3b250c4e9b' => [
                'attributes' => [],
                'icon'       => '',
                'link'       => '/',
                'text'       => 'Home',
            ],
            '69036aa2fcdac6c8fd29fea71aad03943af4a162' => [
                'attributes' => [],
                'icon'       => '<i class="fa-solid fa-file-invoice"></i>',
                'link'       => '/invoices',
                'text'       => 'Invoices',
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
            '75192c92acf8997b8863087bb3428b3b250c4e9b' => [
                'attributes' => [],
                'icon'       => '',
                'link'       => '/',
                'text'       => 'Home',
            ],
            '69036aa2fcdac6c8fd29fea71aad03943af4a162' => [
                'attributes' => [],
                'icon'       => '<i class="fa-solid fa-file-invoice"></i>',
                'link'       => '/invoices',
                'text'       => 'Invoices',
            ],
            '2ffff299327c2b373331240e28352b65f47ef006' => [
                'attributes' => [
                    'class'      => 'breadcrumb-item',
                    'aria-label' => 'breadcrumb',
                ],
                'icon'       => '<i class="fa-solid fa-user"></i>',
                'link'       => '/customers',
                'text'       => 'Customers',
            ],
        ];
        $actual   = $breadcrumbs->toArray();
        $this->assertSame($expected, $actual);

        /**
         * remove()
         */
        $breadcrumbs->remove('Invoices', '/invoices');
        $expected = [
            '75192c92acf8997b8863087bb3428b3b250c4e9b' => [
                'attributes' => [],
                'icon'       => '',
                'link'       => '/',
                'text'       => 'Home',
            ],
            '2ffff299327c2b373331240e28352b65f47ef006' => [
                'attributes' => [
                    'class'      => 'breadcrumb-item',
                    'aria-label' => 'breadcrumb',
                ],
                'icon'       => '<i class="fa-solid fa-user"></i>',
                'link'       => '/customers',
                'text'       => 'Customers',
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
