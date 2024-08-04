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

namespace Phalcon\Tests\Unit\Assets\Manager;

use Countable;
use IteratorAggregate;
use Phalcon\Assets\Collection;
use Phalcon\Assets\Manager;
use Phalcon\Html\Escaper;
use Phalcon\Html\TagFactory;
use Phalcon\Tests\UnitTestCase;

final class GetCollectionsTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Assets\Manager :: getCollections()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-19
     */
    public function testAssetsManagerGetCollections(): void
    {
        $manager = new Manager(new TagFactory(new Escaper()));

        $manager->set('hangout1', new Collection());
        $manager->set('hangout2', new Collection());

        $collections = $manager->getCollections();

        $this->assertCount(2, $collections);
        foreach ($collections as $collection) {
            $this->assertInstanceOf(Collection::class, $collection);
            $this->assertInstanceOf(Countable::class, $collection);
            $this->assertInstanceOf(IteratorAggregate::class, $collection);
        }
    }
}
