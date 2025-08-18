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

use Phalcon\Di\Di;
use Phalcon\Di\Exception;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\Alpha;
use Phalcon\Filter\Validation\Validator\Email;
use Phalcon\Filter\Validation\Exception as ValidationException;
use Phalcon\Tests\Fixtures\Filter\Validation\ValueAttributeFixture;
use Phalcon\Tests\Fixtures\Filter\Validation\ValueMethodFixture;
use Phalcon\Tests\Fixtures\Filter\Validation\ValuePropertyFixture;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\AbstractUnitTestCase;
use stdClass;

final class GetValueTest extends AbstractUnitTestCase
{
    use DiTrait;

    /**
     * Tests Phalcon\Filter\Validation :: getValue()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-04-16
     */
    public function testFilterValidationGetValueEntity(): void
    {
        $this->setNewFactoryDefault();

        $alpha = new Alpha();
        $email = new Email();
        $user  = new stdClass();
        $data  = [
            'name' => 'Leonidas',
            'city' => 'Sparta',
        ];

        $validation = new Validation();

        $validation->bind($user, $data);

        $validation
            ->add('name', $alpha)
            ->add('email', $email);

        $expected = 'Leonidas';
        $value = $validation->getValue('name');
        $this->assertSame($expected, $value);
    }

    /**
     * Tests Phalcon\Filter\Validation :: getValue()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-04-16
     */
    public function testFilterValidationGetValueData(): void
    {
        $this->setNewFactoryDefault();

        $alpha = new Alpha();
        $email = new Email();
        $data  = [
            'name' => 'Leonidas',
            'city' => 'Sparta',
        ];

        $validation = new Validation();

        $validation->bind(null, $data);

        $validation
            ->add('name', $alpha)
            ->add('email', $email);

        $expected = 'Leonidas';
        $value = $validation->getValue('name');
        $this->assertSame($expected, $value);
    }

    /**
     * Tests Phalcon\Filter\Validation :: getValue()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-04-16
     */
    public function testFilterValidationGetValueDataNull(): void
    {
        $this->setNewFactoryDefault();

        $alpha = new Alpha();
        $email = new Email();
        $data  = [
            'name' => null,
            'city' => 'Sparta',
        ];

        $validation = new Validation();

        $validation->bind(null, $data);

        $validation
            ->add('name', $alpha)
            ->add('email', $email);

        $value = $validation->getValue('name');
        $this->assertNull($value);
    }

    /**
     * Tests Phalcon\Filter\Validation :: getValue()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-04-16
     */
    public function testFilterValidationGetValueDataFilters(): void
    {
        $this->setNewFactoryDefault();

        $alpha = new Alpha();
        $email = new Email();
        $data  = [
            'name' => ' Leonidas ',
            'city' => 'Sparta',
        ];

        $validation = new Validation();

        $validation
            ->setFilters('name', 'trim')
            ->setFilters('email', 'lower')
            ->add('name', $alpha)
            ->add('email', $email)
            ->bind(null, $data)
        ;

        $expected = 'Leonidas';
        $actual   = $validation->getValue('name');
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Filter\Validation :: getValue()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-04-16
     */
    public function testFilterValidationGetValueDataFilterException(): void
    {
        $container = new Di();
        $alpha     = new Alpha();
        $email     = new Email();
        $data      = [
            'name' => ' Leonidas ',
            'city' => 'Sparta',
        ];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            "Service 'filter' was not found in the dependency injection container"
        );
        $validation = new Validation();
        $validation->setDI($container);

        $validation
            ->setFilters('name', 'trim')
            ->setFilters('email', 'lower')
            ->add('name', $alpha)
            ->add('email', $email)
            ->bind(null, $data)
        ;


        $validation->getValue('name');
    }

    /**
     * Tests Phalcon\Filter\Validation :: getValue()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-04-16
     */
    public function testFilterValidationGetValueDataFiltersAttribute(): void
    {
        $this->setNewFactoryDefault();

        $alpha = new Alpha();
        $email = new Email();
        $entity = new ValueAttributeFixture(' Leonidas ');

        $validation = new Validation();

        $validation
            ->setFilters('name', 'trim')
            ->setFilters('email', 'lower')
            ->add('name', $alpha)
            ->add('email', $email)
            ->bind($entity, [])
        ;

        $expected = 'Leonidas';
        $actual   = $validation->getValue('name');
        $this->assertSame($expected, $actual);

        $expected = 'Leonidas';
        $actual   = $entity->readAttribute();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Filter\Validation :: getValue()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-04-16
     */
    public function testFilterValidationGetValueDataFiltersMethod(): void
    {
        $this->setNewFactoryDefault();

        $alpha = new Alpha();
        $email = new Email();
        $entity = new ValueMethodFixture(' Leonidas ');

        $validation = new Validation();

        $validation
            ->setFilters('name', 'trim')
            ->setFilters('email', 'lower')
            ->add('name', $alpha)
            ->add('email', $email)
            ->bind($entity, [])
        ;

        $expected = 'Leonidas';
        $actual   = $validation->getValue('name');
        $this->assertSame($expected, $actual);

        $expected = 'Leonidas';
        $actual   = $entity->getName();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Filter\Validation :: getValue()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-04-16
     */
    public function testFilterValidationGetValueDataFiltersProperty(): void
    {
        $this->setNewFactoryDefault();

        $alpha = new Alpha();
        $email = new Email();
        $entity = new ValuePropertyFixture(' Leonidas ');

        $validation = new Validation();

        $validation
            ->setFilters('name', 'trim')
            ->setFilters('email', 'lower')
            ->add('name', $alpha)
            ->add('email', $email)
            ->bind($entity, [])
        ;

        $expected = 'Leonidas';
        $actual   = $validation->getValue('name');
        $this->assertSame($expected, $actual);

        $expected = 'Leonidas';
        $actual   = $entity->name;
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Filter\Validation :: getValue()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-04-16
     */
    public function testFilterValidationGetValueDataException(): void
    {
        $alpha = new Alpha();
        $email = new Email();

        $validation = new Validation();

        $validation
            ->add('name', $alpha)
            ->add('email', $email);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('There is no data to validate');

        $validation->getValue('name');
    }
}
