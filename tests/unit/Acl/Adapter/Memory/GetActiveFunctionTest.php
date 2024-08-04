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

namespace Phalcon\Tests\Unit\Acl\Adapter\Memory;

use Closure;
use Phalcon\Acl\Adapter\Memory;
use Phalcon\Acl\Component;
use Phalcon\Acl\Role;
use Phalcon\Tests\UnitTestCase;

final class GetActiveFunctionTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Acl\Adapter\Memory :: getActiveFunction()
     *
     * @return void
     *
     * @author  Wojciech Slawski <jurigag@gmail.com>
     * @since   2017-01-13
     */
    public function testAclAdapterMemoryGetActiveFunction(): void
    {
        $function = function ($a) {
            return $a;
        };

        $acl = new Memory();
        $acl->addRole(new Role('Guests'));
        $acl->addComponent(
            new Component('Post'),
            ['index', 'update', 'create']
        );

        $acl->allow('Guests', 'Post', 'create', $function);

        $actual = $acl->isAllowed(
            'Guests',
            'Post',
            'create',
            [
                'a' => 1,
            ]
        );
        $this->assertTrue($actual);

        $returnedFunction = $acl->getActiveFunction();

        $class = Closure::class;
        $actual = $returnedFunction;
        $this->assertInstanceOf($class, $actual);

        $expected = 1;
        $actual   = $function(1);
        $this->assertSame($expected, $actual);

        $expected = 1;
        $actual   = $acl->getActiveFunctionCustomArgumentsCount();
        $this->assertSame($expected, $actual);
    }
}
