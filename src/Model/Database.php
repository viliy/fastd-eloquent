<?php /** @noinspection ALL */

/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @see       https://www.github.com/janhuang
 * @see       https://fastdlabs.com
 */

namespace Zhaqq\Eloquent\Model;

use FastD;
use Medoo\Medoo;
use PDO;

/**
 * Class Database.
 */
class Database extends Medoo
{
    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var string
     */
    protected $lastUseTime;

    /**
     * @var string
     */
    protected $hash = '';

    /**
     * @var PDO
     */
    public $pdo;


    /**
     * Database constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = null)
    {
        $this->config = $options;

        parent::__construct($this->config);

        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $this->pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
    }

    /**
     * reconnect database.
     */
    public function reconnect()
    {
        $this->__construct($this->config);
    }

    /**
     * @return string
     */
    public function getLastUseTime(): string
    {
        return $this->lastUseTime;
    }

    /**
     * @param int $lastUseTime
     *
     * @return $this
     */
    public function setLastUseTime(int $lastUseTime = 0): Database
    {
        !$lastUseTime && $lastUseTime = time();

        $this->lastUseTime = $lastUseTime;

        return $this;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @param string|null $hash
     *
     * @return $this
     */
    public function setHash(string $hash = '')
    {
        !$hash && $hash = md5(uniqid(microtime(true), true)) . rand(100, 10000);
        $this->hash = $hash;

        return $this;
    }
}
