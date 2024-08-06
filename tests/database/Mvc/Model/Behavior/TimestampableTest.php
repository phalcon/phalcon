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

namespace Phalcon\Tests\Database\Mvc\Model\Behavior;

use DateTime;
use Phalcon\Events\Event;
use Phalcon\Events\Manager as EventManager;
use Phalcon\Tests\DatabaseTestCase;
use Phalcon\Tests\Fixtures\Migrations\InvoicesMigration;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\Models\Invoices;
use Phalcon\Tests\Models\InvoicesBehavior;

use function uniqid;

final class TimestampableTest extends DatabaseTestCase
{
    use DiTrait;

    public function setUp(): void
    {
        $this->setNewFactoryDefault();
        $this->setDatabase();

        $connection = self::getConnection();
        (new InvoicesMigration($connection));
    }

    /**
     * Tests Phalcon\Mvc\Model\Behavior :: Timestampable()
     *
     * @author Jeremy PASTOURET <https://github.com/jenovateurs>
     * @since  2020-10-03
     *
     * @group  common
     */
    public function testMvcModelBehaviorTimestampable(): void
    {
        /** Add row to Timestampable then */
        $title = uniqid('inv-');

        $invoice                  = new InvoicesBehavior();
        $invoice->inv_cst_id      = 2;
        $invoice->inv_status_flag = Invoices::STATUS_PAID;
        $invoice->inv_title       = $title;
        $invoice->inv_total       = 100.12;

        $date = date('Y-m-d');
        $invoice->save();

        $this->assertNotEmpty($invoice->inv_created_at);

        $secondThreshold = 60;
        $dateFromAssign  = new Datetime($invoice->inv_created_at);
        $dateAfterAssign = new Datetime($date);
        $nInterval       = $dateFromAssign->diff($dateAfterAssign)->format('%s');

        $this->assertLessThan($secondThreshold, $nInterval);
    }

    /**
     * Tests Phalcon\Mvc\Model\Behavior :: Timestampable() - with before create event
     *
     * @author Jeremy PASTOURET <https://github.com/jenovateurs>
     * @since  2020-10-03
     *
     * @group  common
     */
    public function testMvcModelBehaviorTimestampableWithBeforeCreateEvent(): void
    {
        $this->markTestSkipped('See: https://github.com/phalcon/cphalcon/issues/14904');

        /** ADD BeforeDelete event */
        $eventsManager = new EventManager();
        $eventsManager->attach('model:beforeCreate', function (Event $event, $model) {
            return false;
        });

        /** Add row to Timestampable then */
        $title = uniqid('inv-');

        $invoice = new InvoicesBehavior();
        $invoice->setEventsManager($eventsManager);
        $invoice->inv_cst_id      = 2;
        $invoice->inv_status_flag = Invoices::STATUS_PAID;
        $invoice->inv_title       = $title;
        $invoice->inv_total       = 100.12;

        $invoice->save();

        $this->assertEmpty($invoice->inv_created_at);
    }
}
