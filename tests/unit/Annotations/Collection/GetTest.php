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

namespace Phalcon\Tests\Unit\Annotations\Collection;

use Phalcon\Annotations\Annotation;
use Phalcon\Annotations\Collection;
use Phalcon\Annotations\Exception;
use Phalcon\Tests\AbstractUnitTestCase;

final class GetTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Annotations\Collection :: get()
     *
     * @author Jeremy PASTOURET <https://github.com/jenovateurs>
     * @since  2020-01-27
     */
    public function testAnnotationsCollectionGet(): void
    {
        $dataAnnotation = [
            'name' => 'NovAnnotation',
        ];

        $dataAnnotation1 = [
            'name' => 'Phalconatation',
        ];

        $reflectionData = [
            $dataAnnotation,
            $dataAnnotation1,
        ];

        $collection = new Collection($reflectionData);
        $annotation = new Annotation($dataAnnotation1);

        $expected = $annotation;
        $actual   = $collection->get('Phalconatation');
        $this->assertEquals($expected, $actual);

        // Check what happens if collection doesn't find an annotation
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            "Collection does not have an annotation called 'NoExist'"
        );

        $collection = new Collection();
        $collection->get('NoExist');
    }
}
