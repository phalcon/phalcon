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

use Phalcon\Assets\Inline;
use Phalcon\Assets\Manager;
use Phalcon\Html\Escaper;
use Phalcon\Html\TagFactory;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\Test;

use function ob_end_clean;
use function ob_get_contents;
use function ob_start;

use const PHP_EOL;

final class AddInlineCodeTest extends AbstractUnitTestCase
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
     * Tests Phalcon\Assets\Manager :: addInlineCode()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-19
     */
    #[Test]
    public function testAssetsManagerAddInlineCode(): void
    {
        $manager = new Manager(new TagFactory(new Escaper()));

        $asset = new Inline('css', 'p {color: #000099}');
        $manager->addInlineCode($asset);

        $expected = "<style>p {color: #000099}</style>" . PHP_EOL;

        ob_start();
        $manager->outputInlineCss();
        $actual = ob_get_contents();
        ob_end_clean();

        $this->assertSame($expected, $actual);
    }
}
