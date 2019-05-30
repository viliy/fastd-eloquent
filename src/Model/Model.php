<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @see       https://www.github.com/janhuang
 * @see       https://fastdlabs.com
 */

namespace Zhaqq\Eloquent\Model;


/**
 * Class Model.
 */
class Model
{
    /**
     * @var Database
     */
    protected $db;

    /**
     * @var
     */
    protected $table;

    /**
     * @var bool
     */
    protected $relaseDb = true;

    protected $connection = 'default';

    /**
     * Model constructor.
     *
     * @param Database $database
     */
    public function __construct(?Database $database = null)
    {
        if (empty($database)) {
            $database = db_pool($this->connection);
        }

        $this->setDatabase($database);
    }

    public function channelGet()
    {
        $channel = new \Swoole\Coroutine\Channel();
        go(function () use ($channel) {
            $data = $this->get();
            $channel->push($data);
        });
        \Swoole\Coroutine::sleep(0.1);

        return $channel->pop();
    }

    public function get()
    {
        \Swoole\Coroutine::sleep(0.1);
        return $this->db->get(
            $this->getTable(),
            [
                'id',
                'name',
                'promotion_id'
            ],
            [
                'id' => 1
            ]
        );
    }

    /**
     * @return Database
     */
    public function getDatabase()
    {
        if (empty($this->db)) {
            $this->setDatabase(db_pool($this->connection));
        }

        return $this->db;
    }

    /**
     * @param Database $db
     */
    protected function setDatabase(Database $db): void
    {
        $this->db = $db;
    }


    /**
     * @param Database $db
     *
     * @return Model
     */
    public static function init(Database $db)
    {
        return new static($db);
    }

    /**
     * @return string
     */
    public function getTable()
    {
        if (empty($this->table)) {
            $this->setTable();
        }

        return $this->table;
    }

    /**
     * @param string|null $table
     */
    public function setTable($table = null): void
    {
        if (empty($table)) {
            $table = basename(str_replace('\\', '/', static::class)) . 's';
        }
        $this->table = strtolower($table);
    }

    public function __destruct()
    {
        echo __CLASS__ . __METHOD__ . PHP_EOL;
        $this->relaseDb && app()->get('db_pool')->push($this->db, $this->connection);
        $this->db = null;
    }
}
