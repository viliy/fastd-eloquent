<?php

namespace Zhaqq\Eloquent;

use FastD\Container\Container;
use FastD\Container\ServiceProviderInterface;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Events\Dispatcher;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

/**
 * Class EloquentServiceProvider
 * @package Zhqq\Eloquent
 */
class EloquentServiceProvider implements ServiceProviderInterface
{

    /**
     * @var Manager
     */
    protected $capsule;

    /**
     * @param Container $container
     */
    public function register(Container $container): void
    {
        $this->capsule = new Manager();
        $this->setConnections($container);
        $this->capsule->bootEloquent();
        $this->setEvent($container);
        $this->setPageAndPathResolver($container);
        $container->add('eloquent', $this->capsule);
    }

    /**
     * @param Container $container
     */
    protected function setConnections(Container $container)
    {
        $configs = $container->get('config')->get('database', []);
        $this->capsule->getDatabaseManager()->setDefaultConnection(array_key_first($configs));

        foreach ($configs as $name => $config) {
            if (isset($config['read'])) {
                $connect = [
                    'driver'   => 'mysql',
                    'read'     => $config['read'],
                    'write'    => $config['write'],
                    'port'     => $config['port'],
                    'database' => $config['name'],
                    'username' => $config['user'],
                    'password' => $config['pass'] ?? '',
                    'charset'  => $config['charset'],
                ];
            } else {
                $connect = [
                    'driver'   => 'mysql',
                    'host'     => $config['host'],
                    'port'     => $config['port'],
                    'database' => $config['name'],
                    'username' => $config['user'],
                    'password' => $config['pass'],
                    'charset'  => $config['charset'],
                ];
            }
            $this->capsule->addConnection($connect, $name);
        }
    }

    /**
     * event dispatcher setting
     *
     * @param Container $container
     */
    protected function setEvent(Container $container)
    {
        $eventDispatcher = new Dispatcher();
        $this->capsule->setEventDispatcher($eventDispatcher);
        $container['event'] = $eventDispatcher;
    }

    /**
     * page setting
     *
     * @param Container $container
     */
    protected function setPageAndPathResolver(Container $container)
    {
        Paginator::currentPageResolver(function ($pageName) use ($container) {
            return $container->get('request')->getParam($pageName, 1);
        });
        Paginator::currentPathResolver(function () use ($container) {
            return $container->get('request')->getUri();
        });
        LengthAwarePaginator::currentPageResolver(function ($pageName) use ($container) {
            return $container->get('request')->getParam($pageName, 1);

        });
        LengthAwarePaginator::currentPathResolver(function () use ($container) {
            return $container->get('request')->getUri();
        });
    }
}
