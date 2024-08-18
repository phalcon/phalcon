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

namespace Phalcon\Tests\Unit\Tag;

use Phalcon\Http\Response;
use Phalcon\Tag;
use Phalcon\Tests\AbstractUnitTestCase;
use Phalcon\Tests\Fixtures\Helpers\AbstractTagSetup;
use Phalcon\Tests\Fixtures\Traits\DiTrait;

class PreloadTest extends AbstractUnitTestCase
{
    use DiTrait;

    public function setUp(): void
    {
        $this->newDi();
        $this->setDiService('escaper');
        $this->setDiService('url');
        $this->setDiService('response');

        Tag::setDI($this->container);
    }

    /**
     * Tests Phalcon\Tag :: image() - array as a parameter
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-05
     */
    public function testTagPreload(): void
    {
        $options  = [
            '/1',
            [
                'as' => 'image',
                'nopush' => true,
            ],
        ];

        $expected = '/1';
        $actual   = Tag::preload($options);
        $this->assertSame($expected, $actual);

        /** @var Response $response */
        $response = $this->container->getShared('response');

        $expected = [
            'Link: </1>; rel="preload"; as="image"; nopush' => null,
        ];
        $actual   = $response->getHeaders()->toArray();
        $this->assertEquals($expected, $actual);
    }
}
