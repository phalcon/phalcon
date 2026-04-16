<?php

use Phalcon\Config\Config;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Application;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Router;
use Phalcon\Mvc\Url;
use Phalcon\Mvc\View;

$container = new FactoryDefault();

/**
 * Load environment
 */
loadIni();
loadFolders();
loadDefined();

/**
 * Config
 */
$configFile = [
    'application' => [
        'baseUri'        => '/',
        'staticUri'      => '/',
        'timezone'       => 'UTC',
        'controllersDir' => rootDir('tests/support/Controllers/'),
        'modelsDir'      => rootDir('tests/support/Models/'),
        'modulesDir'     => rootDir('tests/support/Modules/'),
        'viewsDir'       => rootDir('tests/support/assets/views/'),
        'resultsetsDir'  => rootDir('tests/_data/fixtures/Resultsets/'),
        'tasksDir'       => rootDir('tests/_data/fixtures/Tasks/'),
    ]
];

$config = new Config($configFile);

$container->setShared('config', $config);

/**
 * View
 */
$container->setShared(
    'view',
    function () use ($configFile) {
        $view = new View();

        $view->setViewsDir(
            $configFile['application']['viewsDir']
        );

        return $view;
    }
);

/**
 * The URL component is used to generate all kind of urls in the
 * application
 */
$container->setShared(
    'url',
    function () use ($configFile) {
        $url = new Url();

        $url->setStaticBaseUri(
            $configFile['application']['staticUri']
        );

        $url->setBaseUri(
            $configFile['application']['baseUri']
        );

        return $url;
    }
);

/**
 * Router
 */
$container->setShared(
    'router',
    function () {
        return new Router(false);
    }
);

/**
 * Dispatcher
 */
$container->set('dispatcher', Dispatcher::class);

$application = new Application();
$application->setDI($container);

FactoryDefault::setDefault($container);

return $application;
