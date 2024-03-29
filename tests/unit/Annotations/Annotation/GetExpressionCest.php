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
use UnitTester;

class GetExpressionCest
{
    private $PHANNOT_T_STRING = 303;

    /**
     * Tests Phalcon\Annotations\Annotation :: getExpression()
     *
     * @author Jeremy PASTOURET <https://github.com/jenovateurs>
     * @since  2020-01-22
     */
    public function annotationsAnnotationGetExpression(UnitTester $I)
    {
        $I->wantToTest('Annotations\Annotation - getExpression()');

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

        $expr = [
            [
                'expr' => $oneExpr,
            ],
            [
                'expr' => $twoExpr,
            ],
        ];

        $annotation = new Annotation([
            'name'      => 'NovAnnotation',
            'arguments' => $expr,
        ]);

        $I->assertSame($annotation->GetExpression($oneExpr), $value);
        $I->assertSame($annotation->GetExpression($twoExpr), $value1);
    }
}
