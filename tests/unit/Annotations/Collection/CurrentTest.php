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
use Phalcon\Tests\AbstractUnitTestCase;

final class CurrentTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Annotations\Collection :: current()
     *
     * @author Jeremy PASTOURET <https://github.com/jenovateurs>
     * @since  2020-01-27
     */
    public function testAnnotationsCollectionCurrent(): void
    {
        $collection = new Collection();

        $this->assertFalse($collection->current());
    }

    /**
     * Tests Phalcon\Annotations\Collection :: current() with annotations
     *
     * @author Jeremy PASTOURET <https://github.com/jenovateurs>
     * @since  2020-01-27
     */
    public function testAnnotationsCollectionCurrentWithAnnotations(): void
    {
        $dataAnnotation = [
            'name' => 'NovAnnotation',
        ];

        $dataAnnotation1 = [
            'name' => 'NovAnnotation1',
        ];

        $reflectionData = [
            $dataAnnotation,
            $dataAnnotation1,
        ];

        $collection = new Collection($reflectionData);
        $annotation = new Annotation($dataAnnotation);

        $this->assertEquals($annotation, $collection->current());
    }
}
