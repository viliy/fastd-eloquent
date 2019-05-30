<?php
/**
 * @author: ZhaQiu
 * @time  : 2019-05-28
 */

namespace Zhaqq\Eloquent\Pool;

use Zhaqq\Eloquent\Model\Database;

/**
 * Class MysqlPool
 * @package Zhaqq\Eloquent\Pool
 */
class MysqlPool
{
    /**
     * @var PoolAbstract[]
     */
    protected $pools;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var string
     */
    protected $hash;


    /**
     * MysqlPool constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return $this
     */
    public function initPool()
    {
        if (!$this->pools) {
            foreach ($this->config as $key => $value) {
                $this->pools[$key] = new Pool($value);
                $this->watcher($key);
            }
        }

        return $this;
    }

    /**
     * @param Database     $connection
     * @param              $name
     */
    public function push(Database $connection, $name)
    {
        if (!isset($this->pools[$name])) {
            throw new \LogicException("pool: $name is undefined");
        }

        $this->pools[$name]->push($connection);
    }

    /**
     * @param $name
     *
     * @return \FastD\Model\Database
     */
    public function pop($name)
    {
        $connection = $this->pools[$name]->pop();
        if (!$connection) {
            // 防止为false的情况
            $connection = $this->pools[$name]->pop();
        }

        return $connection;
    }

    protected function watcher($key, $time = 10000)
    {
        \Swoole\Timer::tick($time, function () use ($key) {
            $this->pools[$key]->watcher();
        });
    }
}