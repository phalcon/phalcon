<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Filter\Validation;

use Phalcon\Filter\Validation;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Phalcon\Tests\Unit\Filter\Validation\Fake\FakeValidatorCompositeRedeclared;
use Phalcon\Tests\Unit\Filter\Validation\Fake\FakeValidatorRedeclared;

final class RedeclaredPropertiesTest extends AbstractUnitTestCase
{
    /**
     * A validator can declare its template with no type, as in cphalcon.
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     *
     * @issue  https://github.com/phalcon/phalcon/issues/840
     */
    public function testFilterValidationRedeclaredTemplate(): void
    {
        $validation = new Validation();
        $validation->add('name', new FakeValidatorRedeclared());

        $messages = $validation->validate(['name' => 'John']);

        $this->assertCount(1, $messages);
        $this->assertSame('Field name is not valid here', $messages[0]->getMessage());
    }

    /**
     * A composite validator can declare its validators with no type, as in
     * cphalcon.
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     *
     * @issue  https://github.com/phalcon/phalcon/issues/840
     */
    public function testFilterValidationRedeclaredValidators(): void
    {
        $validator = new FakeValidatorCompositeRedeclared();

        $this->assertSame([], $validator->getValidators());
    }
}
