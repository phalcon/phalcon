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

use Phalcon\Filter\Validation\Validator\PresenceOf;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Phalcon\Tests\Unit\Filter\Validation\Fake\FakeValidationOverride;

final class OverrideSignatureTest extends AbstractUnitTestCase
{
    /**
     * A validation can override validate() with the cphalcon signature.
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     *
     * @issue  https://github.com/phalcon/phalcon/issues/841
     */
    public function testFilterValidationValidateOverride(): void
    {
        $validation = new FakeValidationOverride();
        $validation->add('name', new PresenceOf());

        $messages = $validation->validate(['name' => '']);

        $this->assertCount(1, $messages);
    }
}
