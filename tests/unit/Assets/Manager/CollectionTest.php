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
use Phalcon\Tests\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\Test;

final class CollectionTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Assets\Manager :: collection()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-19
     */
    #[Test]
    public function testAssetsManagerCollection(): void
    {
        $manager = new Manager(new TagFactory(new Escaper()));

        $collection = $manager->collection('hangout1');
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertInstanceOf(Countable::class, $collection);
        $this->assertInstanceOf(IteratorAggregate::class, $collection);

        $collection = $manager->collection('hangout2');
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertInstanceOf(Countable::class, $collection);
        $this->assertInstanceOf(IteratorAggregate::class, $collection);

        $collections = $manager->getCollections();

        $this->assertCount(2, $collections);
        foreach ($collections as $collection) {
            $this->assertInstanceOf(Collection::class, $collection);
            $this->assertInstanceOf(Countable::class, $collection);
            $this->assertInstanceOf(IteratorAggregate::class, $collection);
        }
    }
}
