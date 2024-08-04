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
use Phalcon\Tests\UnitTestCase;

final class GetAnnotationsTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Annotations\Collection :: getAnnotations()
     *
     * @author Jeremy PASTOURET <https://github.com/jenovateurs>
     * @since  2020-01-27
     */
    public function testAnnotationsCollectionGetAnnotations(): void
    {
        $dataAnnotation = [
            'name' => 'NovAnnotation',
        ];

        $dataAnnotation1 = [
            'name' => 'NovAnnotation1',
        ];

        $dataAnnotation2 = [
            'name' => 'NovAnnotation',
        ];

        $reflectionData = [
            $dataAnnotation,
            $dataAnnotation1,
            $dataAnnotation2,
        ];

        $collection = new Collection($reflectionData);

        $annotation  = new Annotation($dataAnnotation);
        $annotation1 = new Annotation($dataAnnotation1);
        $annotation2 = new Annotation($dataAnnotation2);

        $resultAnnotation = [
            $annotation,
            $annotation1,
            $annotation2,
        ];

        // Need to find two annotations with the name NovAnnotation
        $expected = $resultAnnotation;
        $actual   = $collection->getAnnotations();
        $this->assertEquals($expected, $actual);
    }
}
