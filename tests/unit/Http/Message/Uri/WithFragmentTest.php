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

namespace Phalcon\Tests\Unit\Http\Message\Uri;

use Phalcon\Http\Message\Uri;
use Phalcon\Tests\AbstractUnitTestCase;

use function sprintf;

final class WithFragmentTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\Uri :: withFragment()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-09
     */
    public function testHttpMessageUriWithFragment(): void
    {
        $query = 'https://phalcon:secret@dev.phalcon.ld:8080/action?param=value#%s';

        $uri = new Uri(
            sprintf($query, 'frag')
        );

        $newInstance = $uri->withFragment('newspaper');
        $this->assertNotSame($uri, $newInstance);

        $expected = 'newspaper';
        $actual   = $newInstance->getFragment();
        $this->assertSame($expected, $actual);

        $expected = sprintf($query, 'newspaper');
        $actual   = (string)$newInstance;
        $this->assertSame($expected, $actual);

        $newInstance = $uri->withFragment('#newspaper');
        $this->assertNotSame($uri, $newInstance);

        $expected = '%23newspaper';
        $actual   = $newInstance->getFragment();
        $this->assertSame($expected, $actual);

        $expected = sprintf($query, '%23newspaper');
        $actual   = (string)$newInstance;
        $this->assertSame($expected, $actual);
    }
}
