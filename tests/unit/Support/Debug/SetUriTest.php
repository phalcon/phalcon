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

namespace Phalcon\Tests\Unit\Support\Debug;

use Phalcon\Support\Debug;
use Phalcon\Tests\AbstractUnitTestCase;

final class SetUriTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Debug :: setUri()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSupportDebugSetUri(): void
    {
        $debug = new Debug();
        $uri   = 'https://something.com/test/';

        $expected = sprintf(
            '<link rel="stylesheet" type="text/css" ' .
            'href="%1$sassets/jquery-ui/themes/ui-lightness/jquery-ui.min.css" />' .
            '<link rel="stylesheet" type="text/css" ' .
            'href="%1$sassets/jquery-ui/themes/ui-lightness/theme.css" />' .
            '<link rel="stylesheet" type="text/css" ' .
            'href="%1$sthemes/default/style.css" />',
            $uri
        );

        $debug->setUri($uri);

        $this->assertSame(
            $expected,
            $debug->getCssSources()
        );
    }
}
