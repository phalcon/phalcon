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

namespace Phalcon\Tests\Unit\Assets\Manager;

use Phalcon\Assets\Manager;
use Phalcon\Html\Escaper;
use Phalcon\Html\TagFactory;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\Test;

use function dataDir;

final class AddInlineJsTest extends AbstractUnitTestCase
{
    use DiTrait;

    /**
     * executed before each test
     */
    public function setUp(): void
    {
        $this->newDi();
        $this->setDiService('escaper');
        $this->setDiService('url');
    }

    /**
     * executed after each test
     */
    public function tearDown(): void
    {
        $this->resetDi();
    }

    /**
     * Tests Phalcon\Assets\Manager :: addInlineJs()
     *
     * @issue https://github.com/phalcon/cphalcon/issues/11409
     */
    #[Test]
    public function testAssetsManagerAddInlineJs(): void
    {
        $manager = new Manager(new TagFactory(new Escaper()));

        $jsFile = dataDir('assets/assets/signup.js');
        $js     = file_get_contents($jsFile);

        $manager->addInlineJs($js);

        $expected = "<script type=\"application/javascript\">{$js}</script>" . PHP_EOL;

        ob_start();
        $manager->outputInlineJs();
        $actual = ob_get_contents();
        ob_end_clean();

        $this->assertSame($expected, $actual);
    }
}
