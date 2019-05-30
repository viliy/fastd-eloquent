<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @see       https://www.github.com/janhuang
 * @see       https://fastdlabs.com
 */

namespace FastD\ServiceProvider;

use FastD\Container\Container;
use FastD\Container\ServiceProviderInterface;
use Zhaqq\Eloquent\Pool\MysqlPool;

/**
 * Class DatabaseServiceProvider.
 */
class PoolDatabaseServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container
     */
    public function register(Container $container): void
    {
        $config = config()->get('database', []);

        $container->add('db_pool', new MysqlPool($config));

        unset($config);
    }
}
