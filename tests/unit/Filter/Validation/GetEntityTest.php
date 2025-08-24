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
use Phalcon\Tests\AbstractUnitTestCase;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use stdClass;

final class GetEntityTest extends AbstractUnitTestCase
{
    use DiTrait;

    /**
     * Tests Phalcon\Filter\Validation :: getEntity()
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-04-17
     */
    public function testFilterValidationGetEntity(): void
    {
        $user = new stdClass();

        $validation = new Validation();

        $validation->setEntity($user);

        $expected = $user;
        $actual   = $validation->getEntity();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Filter\Validation :: getEntity() - with filters
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2025-08-12
     */
    public function testFilterValidationGetEntityWithFilters(): void
    {
        $this->setNewFactoryDefault();

        $user       = new stdClass();
        $user->name = '';

        $validation = new Validation();
        $validation->setFilters('name', ['trim', 'striptags']);
        $validation->validate(['name' => ' John <script>Chris</script>'], $user);

        $this->assertSame(
            'John Chris',
            $validation->getEntity()->name
        );

        $this->assertSame(
            'John Chris',
            $validation->getValue('name')
        );
    }

    /**
     * Tests Phalcon\Filter\Validation :: getEntity() - using bind() with whitelist fields
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2025-08-12
     */
    public function testFilterValidationGetEntityUsingBindWithWhitelistFields(): void
    {
        $this->setNewFactoryDefault();

        $user           = new stdClass();
        $user->name     = '';
        $user->email    = '';
        $user->password = '';

        $postData = [
            'name'     => 'John Doe',
            'email'    => 'name@example.com',
            'password' => 'new_password'
        ];

        $validation = new Validation();
        $validation
            ->bind($user, $postData, ['name', 'password'])
            ->validate();

        $this->assertSame('John Doe', $validation->getEntity()->name);
        $this->assertSame('John Doe', $validation->getValue('name'));

        $this->assertSame('', $validation->getEntity()->email);
        $this->assertSame('', $validation->getValue('email'));

        $this->assertSame('new_password', $validation->getEntity()->password);
        $this->assertSame('new_password', $validation->getValue('password'));
    }

    /**
     * Tests Phalcon\Filter\Validation :: getEntity() - using validate() with whitelist fields
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2025-08-12
     */
    public function testFilterValidationGetEntityUsingValidateWithWhitelistFields(): void
    {
        $this->setNewFactoryDefault();

        $user           = new stdClass();
        $user->name     = '';
        $user->email    = '';
        $user->password = '';

        $postData = [
            'name'     => 'John Doe',
            'email'    => 'name@example.com',
            'password' => 'new_password'
        ];

        $validation = new Validation();
        $validation->validate($postData, $user, ['name', 'password']);

        $this->assertSame('John Doe', $validation->getEntity()->name);
        $this->assertSame('John Doe', $validation->getValue('name'));

        $this->assertSame('', $validation->getEntity()->email);
        $this->assertSame('', $validation->getValue('email'));

        $this->assertSame('new_password', $validation->getEntity()->password);
        $this->assertSame('new_password', $validation->getValue('password'));
    }
}
