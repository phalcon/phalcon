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

namespace Phalcon\Tests\Unit\Mvc\Model;

use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Phalcon\Tests\Unit\Mvc\Model\Fake\FakeModelRedeclared;
use ReflectionProperty;

final class RedeclaredPropertiesTest extends AbstractUnitTestCase
{
    /**
     * A model can have the columns "transaction", "uniqueKey" and
     * "uniqueParams" as public properties with no type, as in cphalcon.
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     *
     * @issue  https://github.com/phalcon/phalcon/issues/840
     */
    public function testMvcModelRedeclaredProperties(): void
    {
        foreach (['transaction', 'uniqueKey', 'uniqueParams'] as $name) {
            $property = new ReflectionProperty(FakeModelRedeclared::class, $name);

            $this->assertTrue($property->isPublic());
            $this->assertFalse($property->hasType());
        }
    }
}
