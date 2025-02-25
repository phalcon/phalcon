<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Fixtures\Traits;

use Phalcon\Annotations\Adapter\Memory;
use Phalcon\Annotations\Annotations;
use Phalcon\Cli\Dispatcher;
use Phalcon\Cli\Router;
use Phalcon\Encryption\Security;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Filter\Filter;
use Phalcon\Html\Escaper;
use Phalcon\Html\TagFactory;
use Phalcon\Mvc\Model\Manager;
use Phalcon\Mvc\Model\MetaData\Adapter\Memory as MetadataMemory;
use Phalcon\Mvc\Model\Transaction\Manager as TransactionManager;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\HelperFactory;

trait CliTrait
{
    /**
     * @return array[]
     */
    public static function getExamplesShortPaths(): array
    {
        return [
            [
                'Feed',
                [
                    'task' => 'feed',
                ],
            ],
            [
                'Feed::get',
                [
                    'task'   => 'feed',
                    'action' => 'get',
                ],
            ],
            [
                'News::Posts::show',
                [
                    'module' => 'News',
                    'task'   => 'posts',
                    'action' => 'show',
                ],
            ],
            [
                'MyApp\\Tasks\\Posts::show',
                [
                    'namespace' => 'MyApp\\Tasks',
                    'task'      => 'posts',
                    'action'    => 'show',
                ],
            ],
            [
                'News::MyApp\\Tasks\\Posts::show',
                [
                    'module'    => 'News',
                    'namespace' => 'MyApp\\Tasks',
                    'task'      => 'posts',
                    'action'    => 'show',
                ],
            ],
            [
                '\\Posts::show',
                [
                    'task'   => 'posts',
                    'action' => 'show',
                ],
            ],
        ];
    }

    public static function getServices(): array
    {
        return [
            [
                'annotations',
                Annotations::class,
            ],
            [
                'annotationsMemory',
                Memory::class,
            ],
            [
                'dispatcher',
                Dispatcher::class,
            ],
            [
                'escaper',
                Escaper::class,
            ],
            [
                'eventsManager',
                EventsManager::class,
            ],
            [
                'filter',
                Filter::class,
            ],
            [
                'helper',
                HelperFactory::class,
            ],
            [
                'modelsManager',
                Manager::class,
            ],
            [
                'modelsMetadata',
                MetadataMemory::class,
            ],
            [
                'router',
                Router::class,
            ],
            [
                'security',
                Security::class,
            ],
            [
                'tag',
                TagFactory::class,
            ],
            [
                'transactionManager',
                TransactionManager::class,
            ],
            [
                'storageSerializer',
                SerializerFactory::class,
            ],
        ];
    }
}
