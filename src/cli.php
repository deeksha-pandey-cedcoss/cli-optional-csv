<?php

declare(strict_types=1);

use Phalcon\Cli\Console;
use Phalcon\Cli\Dispatcher;
use Phalcon\Di\FactoryDefault\Cli as CliDI;
use Phalcon\Exception as PhalconException;
use Phalcon\Loader;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Cache\Adapter\Stream;
use Phalcon\Storage\SerializerFactory;


$loader = new Loader();

define('BASE_PATH', dirname(__DIR__).'/html/');

require_once './vendor/autoload.php';

$loader->registerDirs(
    [
        BASE_PATH . '/Tasks/',

    ]
);
$loader->registerNamespaces(
    [
        'MyApp\Tasks' => BASE_PATH.'/Tasks/',
    ]
);
$loader->register();

$container  = new CliDI();
$dispatcher = new Dispatcher();

$dispatcher->setDefaultNamespace('MyApp\Tasks');
$container->setShared('dispatcher', $dispatcher);


$container->set(
    'db',
    function () {
        return new Mysql(
            [
                'host'     => 'mysql-server',
                'username' => 'root',
                'password' => 'secret',
                'dbname'   => 'store',
            ]
        );
    }
);
$container->set(
    'mongo',
    function () {
        $mongo = new MongoDB\Client(
            'mongodb+srv://deekshapandey:Deeksha123@cluster0.whrrrpj.mongodb.net/?retryWrites=true&w=majority'
        );

        return $mongo->csv;
    },
    true
);
$container->set(
    'cache',
    function () {
        $serializerFactory = new SerializerFactory();
        $options = [
            'defaultSerializer' => 'Json',
            'lifetime'          => 7200,
            'storageDir'        => BASE_PATH.'/cache',
        ];

        return new Stream($serializerFactory, $options);
    }
);

$console = new Console($container);

$arguments = [];
foreach ($argv as $k => $arg) {
    if ($k === 1) {
        $arguments['task'] = $arg;
    } elseif ($k === 2) {
        $arguments['action'] = $arg;
    } elseif ($k >= 3) {
        $arguments['params'][] = $arg;
    }
}

try {
    $console->handle($arguments);
} catch (PhalconException $e) {
    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    exit(1);
} catch (Throwable $throwable) {
    fwrite(STDERR, $throwable->getMessage() . PHP_EOL);
    exit(1);
} catch (Exception $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(1);
}
