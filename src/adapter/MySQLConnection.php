<?php

namespace Zhaqq\Eloquent\Database;

use Illuminate\Database\MySqlConnection as BaseMySQLConnection;
use PDO;

/**
 * @package Zqhong\FastdEloquent
 */
class MySQLConnection extends BaseMySQLConnection
{
    /**
     * Bind values to their parameters in the given statement.
     *
     * @param  \PDOStatement $statement
     * @param  array $bindings
     * @return void
     */
    public function bindValues($statement, $bindings)
    {
        foreach ($bindings as $key => $value) {
            $statement->bindValue(
                is_string($key) ? $key : $key + 1, $value,
                PDO::PARAM_STR
            );
        }
    }
}