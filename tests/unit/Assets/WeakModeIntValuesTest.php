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

namespace Phalcon\Tests\Unit\Assets;

use Phalcon\Assets\Filters\None;
use Phalcon\Assets\Manager;
use Phalcon\Html\Escaper;
use Phalcon\Html\TagFactory;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;

/**
 * cphalcon uses the PHP weak type rules for an int value that goes to a string
 * target, thus the int becomes a string.
 *
 * @issue https://github.com/phalcon/phalcon/issues/886
 */
final class WeakModeIntValuesTest extends AbstractUnitTestCase
{
    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testAssetsManagerHrefAttributeInt(): void
    {
        $manager = new Manager(new TagFactory(new Escaper()));
        $manager->useImplicitOutput(false);
        $manager->addCss('a.css', true, false, ['href' => 123]);

        $this->assertSame(
            '<link rel="stylesheet" type="text/css" href="/123" />' . PHP_EOL,
            $manager->outputCss()
        );
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testAssetsManagerTargetBasePathInt(): void
    {
        $manager = new Manager(
            new TagFactory(new Escaper()),
            ['targetBasePath' => 123]
        );
        $manager->useImplicitOutput(false);
        $manager->collection('x')
                ->join(false)
                ->addFilter(new None())
                ->addJs('https://example.com/a.js', false)
        ;

        $this->assertSame(
            '<script type="application/javascript" src="/https://example.com/a.js"></script>' . PHP_EOL,
            $manager->outputJs('x')
        );
    }
}
