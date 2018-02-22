<?php

namespace DarkAlchemy\Pu239;

use Envms\FluentPDO\Query;

class Database extends Query
{
    protected $pdo;
    /**
     * Database constructor.
     */
    public function __construct()
    {
        if (!SOCKET) {
            $this->pdo = new \PDO("{$_ENV['DB_CONNECTION']}:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']};dbname={$_ENV['DB_DATABASE']};charset={$_ENV['DB_CHARSET']}", "{$_ENV['DB_USERNAME']}", "{$_ENV['DB_PASSWORD']}");
        } else {
            $this->pdo = new \PDO("{$_ENV['DB_CONNECTION']}:unix_socket={$_ENV['DB_SOCKET']};dbname={$_ENV['DB_DATABASE']};charset={$_ENV['DB_CHARSET']}", "{$_ENV['DB_USERNAME']}", "{$_ENV['DB_PASSWORD']}");
        }

        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_WARNING);
        $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        $this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        $this->pdo->setAttribute(\PDO::ATTR_PERSISTENT, true);

        parent::__construct($this->pdo);
    }

    public function __destruct()
    {
        //$this->pdo = null;
    }
}
