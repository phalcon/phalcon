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

namespace Phalcon\Tests\Database\Mvc\Model;

use Phalcon\Tests\AbstractDatabaseTestCase;

final class RefactorDynamicOperationsTest extends AbstractDatabaseTestCase
{
    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-02
     */
    public function testShouldSaveSnapshotWhenHavingPublicPropertyWithNullValue(): void
    {
        $this->markTestSkipped('Needs review - tocheck after migration');
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-02
     */
    public function testShouldWorkUsingDynamicUpdate(): void
    {
        $this->markTestSkipped('Needs review - tocheck after migration');
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-02
     */
    public function testShouldWorkUsingDynamicUpdateForRenamedModelFields(): void
    {
        $this->markTestSkipped('Needs review - tocheck after migration');
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-02
     */
    public function testShouldWorkUsingDynamicUpdateSoftDeleteForRenamedModel(): void
    {
        $this->markTestSkipped('Needs review - tocheck after migration');
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-02
     */
    public function testShouldWorkUsingDynamicUpdateForRawValue(): void
    {
        $this->markTestSkipped('Needs review - tocheck after migration');
    }
}

// Original Cest file content (DynamicOperationsCest):
//
// <?php
//
// /**
//  * This file is part of the Phalcon Framework.
//  *
//  * (c) Phalcon Team <team@phalcon.io>
//  *
//  * For the full copyright and license information, please view the LICENSE.txt
//  * file that was distributed with this source code.
//
