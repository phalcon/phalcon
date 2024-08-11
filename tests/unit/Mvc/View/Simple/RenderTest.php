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

namespace Phalcon\Tests\Unit\Mvc\View\Simple;

use Phalcon\Mvc\View\Engine\Volt;
use Phalcon\Mvc\View\Exception;
use Phalcon\Tests\Fixtures\Objects\ChildObject;
use Phalcon\Tests\Fixtures\Objects\ParentObject;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\Fixtures\Traits\ViewTrait;
use Phalcon\Tests\AbstractUnitTestCase;

use function dataDir;
use function ob_end_clean;
use function ob_get_level;
use function ob_start;
use function sprintf;

class RenderTest extends AbstractUnitTestCase
{
    use DiTrait;
    use ViewTrait;

    public function setUp(): void
    {
        $this->newDi();
        $this->setDiService('viewSimple');

        ob_start();
    }

    public function tearDown(): void
    {
        if (ob_get_level()) {
            ob_end_clean();
        }
    }

    public function testMvcViewRenderChildobject(): void
    {
        $this->safeDeleteFile(
            dataDir('fixtures/views/currentrender/subobject.volt.php')
        );

        $view = $this->container->get('viewSimple');

        $view->registerEngines(
            [
                '.volt' => Volt::class,
            ]
        );

        $child  = new ChildObject();
        $parent = new ParentObject($child);

        $view->setVar('parentObject', $parent);

        $expected = 'Value';
        $actual   = $view->render('currentrender/subobject');
        $this->assertEquals($expected, $actual);

        $this->safeDeleteFile(
            dataDir('fixtures/views/currentrender/subobject.volt.php')
        );
    }

    /**
     * Tests Phalcon\Mvc\View\Simple :: render()
     *
     * @author Kamil Skowron <git@hedonsoftware.com>
     * @since  2014-05-28
     */
    public function testMvcViewSimpleRender(): void
    {
        $view = $this->container->get('viewSimple');

        $expected = 'here';
        $actual   = $view->render('currentrender/other');
        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Mvc\View\Simple :: render() - filename missing engine
     *
     * @author Kamil Skowron <git@hedonsoftware.com>
     * @since  2014-05-28
     */
    public function testRenderFilenameWithoutEngine(): void
    {
        $this->markTestSkipped('TODO: Check open buffers');
        try {
            $view = $this->container->get('viewSimple');
            $view->setParamToView('name', 'FooBar');
            $view->render('unknown/view');
        } catch (Exception $ex) {
            $view->finish();
            ob_end_clean();
            $actual   = $ex->getMessage();
            $expected = sprintf(
                "View '%sfixtures/views/unknown/view' was not found in the views directory",
                dataDir()
            );
            $this->assertSame($expected, $actual);
        }
    }

    /**
     * Tests Phalcon\Mvc\View\Simple :: render() - missing view
     *
     * @author Kamil Skowron <git@hedonsoftware.com>
     * @since  2014-05-28
     */
    public function testRenderMissingView(): void
    {
        $this->markTestSkipped('TODO: Check open buffers');
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            sprintf(
                "View '%sfixtures/views/unknown/view' was not found in the views directory",
                dataDir()
            )
        );

        $view = $this->container->get('viewSimple');
        $view->render('unknown/view');
    }

    /**
     * Tests Phalcon\Mvc\View\Simple :: render() - with mustache
     *
     * @author Kamil Skowron <git@hedonsoftware.com>
     * @since  2014-05-28
     */
    public function testRenderRenderWithMustache(): void
    {
        $view = $this->container->get('viewSimple');

        $view->setParamToView('name', 'FooBar');

        $view->registerEngines(
            [
                '.mhtml' => Volt::class,
            ]
        );

        $this->assertEquals(
            'Hello FooBar',
            $view->render('mustache/index')
        );

        $this->assertFileExists(
            dataDir('fixtures/views/mustache') . '/index.mhtml.php'
        );

        $this->safeDeleteFile(
            dataDir('fixtures/views/mustache') . '/index.mhtml.php'
        );
    }

    /**
     * Tests Phalcon\Mvc\View\Simple :: render() - standard
     *
     * @author Kamil Skowron <git@hedonsoftware.com>
     * @since  2014-05-28
     */
    public function testRenderStandard(): void
    {
        $view = $this->container->get('viewSimple');

        $expected = 'We are here';
        $actual   = $view->render('simple/index');
        $this->assertEquals($expected, $actual);

        $expected = 'We are here';
        $actual   = $view->getContent();
        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Mvc\View\Simple :: render() - with partials
     *
     * @author Kamil Skowron <git@hedonsoftware.com>
     * @since  2014-05-28
     */
    public function testRenderWithPartials(): void
    {
        $view = $this->container->get('viewSimple');

        $expectedParams = [
            'cool_var' => 'FooBar',
        ];

        $view->partial('partials/partial', $expectedParams);

        $this->assertEquals(
            'Hey, this is a partial, also FooBar',
            $view->getContent()
        );

        $view->setVars($expectedParams);
    }
}
