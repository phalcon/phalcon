<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phalcon\Tests\Unit\Forms\Form;

use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Form;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\AbstractUnitTestCase;

final class IteratorTest extends AbstractUnitTestCase
{
    use DiTrait;

    public function setUp(): void
    {
        $this->newDi();
        $this->setDiService('escaper');
        $this->setDiService('url');
    }

    public function testIterator(): void
    {
        $form = new Form();
        $data = [];

        foreach ($form as $key => $value) {
            $data[$key] = $value->getName();
        }

        $this->assertEquals(
            [],
            $data
        );


        $form->add(
            new Text('name')
        );

        $form->add(
            new Text('telephone')
        );

        foreach ($form as $key => $value) {
            $data[$key] = $value->getName();
        }

        $expected = [
            0 => 'name',
            1 => 'telephone',
        ];

        $this->assertEquals($expected, $data);
    }
}
