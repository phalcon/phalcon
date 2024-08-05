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
use Phalcon\Tests\Models\EntityWithGetter;
use Phalcon\Tests\Models\EntityWithHook;
use Phalcon\Tests\Models\EntityWithPublic;
use Phalcon\Tests\UnitTestCase;

final class GetValueByEntityTest extends UnitTestCase
{
    public const NAME = 'John Doe';

    public function testFilterValidationGetValueByEntityGetter(): void
    {
        $entity = new EntityWithGetter(self::NAME);

        $validation = new Validation();
        $value      = $validation->getValueByEntity($entity, 'name');

        $this->assertSame($entity->getName(), $value);
    }

    /**
     * Tests Phalcon\Filter\Validation :: getValueByEntity()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2021-11-07
     */
    public function testFilterValidationGetValueByEntityPublic(): void
    {
        $entity = new EntityWithPublic(self::NAME);

        $validation = new Validation();
        $value      = $validation->getValueByEntity($entity, 'name');

        $this->assertSame($entity->name, $value);
    }

    public function testFilterValidationGetValueByEntityReadAttribute(): void
    {
        $entity = new EntityWithHook(self::NAME);

        $validation = new Validation();
        $value      = $validation->getValueByEntity($entity, 'name');

        $this->assertSame($entity->readAttribute('name'), $value);
    }
}
