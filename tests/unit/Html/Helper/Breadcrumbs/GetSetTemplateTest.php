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

final class GetSetTemplateTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Html\Breadcrumbs :: getTemplate()/setTemplate()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testHtmlHelperBreadcrumbsGetSetTemplate(): void
    {
        $escaper     = new Escaper();
        $breadcrumbs = new Breadcrumbs($escaper);

        $expected = [
            'main' => '%indent%<nav%attributes%>%delimiter%'
                . '%indent%<ol>%delimiter%'
                . '%items%'
                . '%indent%</ol>%delimiter%'
                . '%indent%</nav>%delimiter%',
            'line' => '%indent%<li%attributes%>'
                . '<a href="%link%">%icon%%text%</a>'
                . '</li>%delimiter%',
            'last' => '%indent%<li><span%attributes%>%text%</span></li>%delimiter%'
        ];
        $actual   = $breadcrumbs->getTemplate();
        $this->assertSame($expected, $actual);

        $main = '<nav><ol>%items%</ol></nav>';
        $line = '<li><a href="%link%">%text%</a></li>';
        $last = '<li>%text%</li>';
        $breadcrumbs->setTemplate($main, $line, $last);

        $expected = [
            'main' => $main,
            'line' => $line,
            'last' => $last,
        ];
        $actual   = $breadcrumbs->getTemplate();
        $this->assertSame($expected, $actual);
    }
}
