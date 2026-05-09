<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Forms\Manager;

use Phalcon\Forms\Element\Email;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Form;
use Phalcon\Forms\Loader\ArrayLoader;
use Phalcon\Forms\Manager;
use Phalcon\Tests\AbstractUnitTestCase;
use stdClass;

final class LoadFormTest extends AbstractUnitTestCase
{
    // -----------------------------------------------------------------------
    // Registration
    // -----------------------------------------------------------------------

    public function testLoadFormRegistersFormByName(): void
    {
        $manager = new Manager();
        $schema  = new ArrayLoader([
            ['type' => 'text', 'name' => 'username'],
        ]);

        $manager->loadForm('login', $schema);

        $this->assertTrue($manager->has('login'));
    }

    public function testLoadFormReturnsForm(): void
    {
        $manager = new Manager();
        $schema  = new ArrayLoader([
            ['type' => 'text', 'name' => 'username'],
        ]);

        $form = $manager->loadForm('login', $schema);

        $this->assertInstanceOf(Form::class, $form);
    }

    public function testLoadFormReturnedFormMatchesStoredForm(): void
    {
        $manager = new Manager();
        $schema  = new ArrayLoader([
            ['type' => 'text', 'name' => 'username'],
        ]);

        $returned = $manager->loadForm('login', $schema);
        $stored   = $manager->get('login');

        $this->assertSame($returned, $stored);
    }

    // -----------------------------------------------------------------------
    // Elements are added
    // -----------------------------------------------------------------------

    public function testLoadFormAddsElements(): void
    {
        $manager = new Manager();
        $schema  = new ArrayLoader([
            ['type' => 'text',     'name' => 'username'],
            ['type' => 'email',    'name' => 'email'],
            ['type' => 'password', 'name' => 'password'],
        ]);

        $form = $manager->loadForm('register', $schema);

        $this->assertCount(3, $form->getElements());
        $this->assertInstanceOf(Text::class,     $form->get('username'));
        $this->assertInstanceOf(Email::class,    $form->get('email'));
        $this->assertInstanceOf(Password::class, $form->get('password'));
    }

    // -----------------------------------------------------------------------
    // Entity
    // -----------------------------------------------------------------------

    public function testLoadFormWithNullEntity(): void
    {
        $manager = new Manager();
        $schema  = new ArrayLoader([
            ['type' => 'text', 'name' => 'username'],
        ]);

        $form = $manager->loadForm('login', $schema, null);

        $this->assertNull($form->getEntity());
    }

    public function testLoadFormPassesEntityToForm(): void
    {
        $manager = new Manager();
        $entity  = new stdClass();
        $schema  = new ArrayLoader([
            ['type' => 'text', 'name' => 'username'],
        ]);

        $form = $manager->loadForm('login', $schema, $entity);

        $this->assertSame($entity, $form->getEntity());
    }

    // -----------------------------------------------------------------------
    // Overwrite
    // -----------------------------------------------------------------------

    public function testLoadFormOverwritesPreviousForm(): void
    {
        $manager = new Manager();

        $manager->loadForm('profile', new ArrayLoader([
            ['type' => 'text', 'name' => 'first_name'],
        ]));

        $manager->loadForm('profile', new ArrayLoader([
            ['type' => 'email', 'name' => 'contact_email'],
        ]));

        $stored = $manager->get('profile');
        $this->assertFalse($stored->has('first_name'));
        $this->assertTrue($stored->has('contact_email'));
    }

    // -----------------------------------------------------------------------
    // Empty schema
    // -----------------------------------------------------------------------

    public function testLoadFormWithEmptySchema(): void
    {
        $manager = new Manager();
        $schema  = new ArrayLoader([]);

        $form = $manager->loadForm('empty', $schema);

        $this->assertTrue($manager->has('empty'));
        $this->assertCount(0, $form->getElements());
    }
}
