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

final class GetSetFiltersTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Filter\Validation :: getFilters()/setFilters()
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-05-27
     */
    public function testFilterValidationGetFilters(): void
    {
        $validation = new Validation();

        $validation
            ->add('name', new PresenceOf())
            ->add('email', new PresenceOf())
        ;

        $validation->setFilters('name', 'trim');
        $validation->setFilters('email', 'lower');

        $expected = [
            'name'  => 'trim',
            'email' => 'lower',
        ];
        $actual   = $validation->getFilters();
        $this->assertEquals($expected, $actual);
    }
}
