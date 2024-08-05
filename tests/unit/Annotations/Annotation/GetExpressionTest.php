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

namespace Phalcon\Tests\Unit\Annotations\Annotation;

use Phalcon\Annotations\Annotation;
use Phalcon\Annotations\Exception;
use Phalcon\Tests\UnitTestCase;

final class GetExpressionTest extends UnitTestCase
{
    private int $PHANNOT_T_ANNOTATION = 300;
    private int $PHANNOT_T_STRING     = 303;

    /**
     * Tests Phalcon\Annotations\Annotation :: getExpression()
     *
     * @author Jeremy PASTOURET <https://github.com/jenovateurs>
     * @since  2020-01-22
     */
    public function testAnnotationsAnnotationGetExpression(): void
    {
        $value  = 'test';
        $value1 = 'test1';

        $oneExpr = [
            'type'  => $this->PHANNOT_T_STRING,
            'value' => $value,
        ];

        $twoExpr = [
            'type'  => $this->PHANNOT_T_STRING,
            'value' => $value1,
        ];

        $threeExpr = [
            'type'  => $this->PHANNOT_T_ANNOTATION,
            'value' => $value1,
        ];

        $expr = [
            [
                'expr' => $oneExpr,
            ],
            [
                'expr' => $twoExpr,
            ],
            [
                'expr' => $threeExpr,
            ],
        ];

        $annotation = new Annotation([
            'name'      => 'NovAnnotation',
            'arguments' => $expr,
        ]);

        $this->assertSame($annotation->getExpression($oneExpr), $value);
        $this->assertSame($annotation->getExpression($twoExpr), $value1);
        $this->assertInstanceOf(
            Annotation::class,
            $annotation->getExpression($threeExpr)
        );
    }

    /**
     * Tests Phalcon\Annotations\Annotation :: getExpression() - exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-01-22
     */
    public function testAnnotationsAnnotationGetExpressionException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The expression 99 is unknown');

        $value = 'test';

        $oneExpr = [
            'type'  => 99,
            'value' => $value,
        ];

        $expr = [
            [
                'expr' => $oneExpr,
            ],
        ];

        $annotation = new Annotation([
            'name'      => 'NovAnnotation',
            'arguments' => $expr,
        ]);

        $annotation->getExpression($oneExpr);
    }
}
