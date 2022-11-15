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

namespace Phalcon\Tests\Integration\Forms\Form;

use IntegrationTester;
use Phalcon\Filter\Validation\Validator\Regex;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Form;
use Phalcon\Messages\Message;
use Phalcon\Messages\Messages;

class HasMessagesForCest
{
    /**
     * Tests Form::hasMessagesFor
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2016-04-03
     */
    public function testFormHasMessagesFor(IntegrationTester $I)
    {
        $telephone = new Text('telephone');
        $telephone->addValidators(
            [
                new Regex(
                    [
                        'pattern' => '/\+44 [0-9]+ [0-9]+/',
                        'message' => 'The telephone has an invalid format',
                    ]
                ),
            ]
        );

        $address = new Text('address');
        $form    = new Form();

        $form->add($telephone);
        $form->add($address);

        $actual = $form->isValid(
            [
                'telephone' => '12345',
                'address'   => 'hello',
            ]
        );

        $I->assertFalse($actual);


        $expected = new Messages(
            [
                new Message(
                    'The telephone has an invalid format',
                    'telephone',
                    Regex::class,
                    0
                ),
            ]
        );

        $I->assertEquals(
            $expected,
            $form->getMessagesFor('telephone')
        );


        $expected = new Messages();

        $I->assertEquals(
            $expected,
            $form->getMessagesFor('address')
        );


        $I->assertTrue(
            $form->hasMessagesFor('telephone')
        );

        $I->assertFalse(
            $form->hasMessagesFor('address')
        );
    }
}
