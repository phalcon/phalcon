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

namespace Phalcon\Tests\Unit\Forms\Element;

use Phalcon\Tests\Fixtures\Traits\FormsTrait;
use Phalcon\Tests\UnitTestCase;

use function array_flip;
use function uniqid;

final class GetSetFiltersTest extends UnitTestCase
{
    use FormsTrait;

    /**
     * Tests Phalcon\Forms\Element\* :: getFilters()/setFilters()/addFilter()
     *
     * @dataProvider getExamples
     *
     * @return void
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2021-12-05
     */
    public function testFormsElementGetSetAddFilters(
        string $class
    ): void {
        $name    = uniqid();
        $data    = [
            'trim',
            'striptags',
        ];
        $flipped = array_flip($data);
        $object  = new $class($name);

        $expected = [];
        $actual   = $object->getFilters();
        $this->assertSame($expected, $actual);

        $object->setFilters($flipped);

        $expected = $flipped;
        $actual   = $object->getFilters();
        $this->assertSame($expected, $actual);

        $object->setFilters('lower');

        $expected = ['lower'];
        $actual   = $object->getFilters();
        $this->assertSame($expected, $actual);

        $object->addFilter('trim');

        $expected = ['lower', 'trim'];
        $actual   = $object->getFilters();
        $this->assertSame($expected, $actual);
    }
}
