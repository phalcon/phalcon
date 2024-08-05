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

namespace Phalcon\Tests\Unit\Filter\Validation;

use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\PresenceOf;
use Phalcon\Tests\UnitTestCase;
use stdClass;

use function date;
use function intval;
use function uniqid;

final class GetSetLabelTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Filter\Validation :: getLabel()/setLabels()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-04-16
     */
    public function testFilterValidationGetLabel(): void
    {
        $validator  = new PresenceOf();
        $validation = new Validation();

        $validation->bind(
            new stdClass(),
            [
                'day'   => date('d'),
                'month' => date('m'),
                'year'  => (string)(intval(date('Y')) + 1),
            ]
        );

        $label = uniqid('lbl-');
        $validation->setLabels(
            [
                'foo' => $label,
            ]
        );
        $validator->validate($validation, 'foo');

        $expected = $label;
        $actual   = $validation->getLabel('foo');
        $this->assertSame($expected, $actual);
    }
}
