<?php
/**
 * @author: ZhaQiu
 * @time  : 2019-05-28
 */

namespace Zhaqq\Eloquent\Pool;

use Swoole\Coroutine\Channel;
use Zhaqq\Eloquent\Model\Database;

class PoolAbstract
{

    protected $maxConnection = 30;

    protected $minConnection = 10;

    protected static $connections = 0;

    protected static $createdConnection = 0;

    protected $heartTime = 3 * 1000;

    protected $timeout = 3 * 1000;

    protected static $useConnectionsArray = [];

    protected static $useConnections = 0;

    /**
     * @var Channel
     */
    public $pool;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var bool
     */
    protected $available = true;

    /**
     * PoolAbstract constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $poolOptions = $config['pool'] ?? [];
        if ($poolOptions) {
            $this->maxConnection = $config['max'] ?? 10;
            $this->minConnection = $config['min'] ?? 3;
            $this->heartTime = $config['heart_time'] ?? 3 * 1000;
            $this->timeout = $config['timeout'] ?? 3 * 1000;
        }
        $this->pool = new Channel($this->maxConnection + 1);
        $this->setMinConnectionPool();
    }

    /**
     */
    public function setMinConnectionPool(): void
    {
        for ($i = 0; $i < $this->minConnection; $i++) {
            $this->push($this->makeDatabase());
        }
    }

    /**
     * @param Database $connection
     *
     * @return bool
     **/
    public function push(Database $connection)
    {
        if ((static::$connections > $this->maxConnection) ||
            $this->pool->isFull() ||
            !isset(static::$useConnectionsArray[$connection->getHash()])
        ) {
            return true;
        }
        unset(static::$useConnectionsArray[$connection->getHash()]);
        $this->pool->push($connection->setLastUseTime());
        static::$useConnections && static::$useConnections--;

        return true;
    }

    /**
     * @param bool $retry
     *
     * @return Database|mixed
     *
     */
    public function pop($retry = true)
    {
        if ($this->pool->isEmpty() && ((static::$useConnections + $this->pool->stats()['queue_num']) > $this->maxConnection)) {
            $connection = $this->makeDatabase();
        } else {
            $connection = $this->pool->pop();
        }
        // retry
        false == $connection && $retry && $connection = $this->pop(false);
        $connection->setLastUseTime()->setHash();
        static::$useConnectionsArray[$connection->getHash()] = $connection->getLastUseTime();

        return $connection;
    }

    /**
     * @return Database
     */
    protected function makeDatabase()
    {
        $config = $this->config;

        $db = new Database(
            [
                'database_type' => isset($config['adapter']) ? $config['adapter'] : 'mysql',
                'database_name' => $config['name'],
                'server' => $config['host'],
                'username' => $config['user'],
                'password' => $config['pass'],
                'charset' => isset($config['charset']) ? $config['charset'] : 'utf8',
                'port' => isset($config['port']) ? $config['port'] : 3306,
                'prefix' => isset($config['prefix']) ? $config['prefix'] : '',
                'option' => isset($config['option']) ? $config['option'] : [],
                'command' => isset($config['command']) ? $config['command'] : [],
            ]
        );
        static::$useConnectionsArray['new'] = time();

        return $db->setLastUseTime()->setHash('new');
    }


    /**
     * 监听者 保持进程池数量稳定
     */
    public function watcher()
    {
        foreach (static::$useConnectionsArray as $key => $value) {
            if (time() - $value > 180) {
                unset(static::$useConnectionsArray[$key]);
            }
        }
        while ((static::$useConnections + $this->pool->stats()['queue_num']) < $this->minConnection) {
            $this->push($this->makeDatabase());
        }
    }
}