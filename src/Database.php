<?php

namespace Pu239;

use Envms\FluentPDO\Query;
use PDO;

/**
 * Class Database.
 */
class Database extends Query
{
    protected $pdo;
    protected $config;

    public function __construct()
    {
        global $site_config;

        $this->config = $site_config;
        if (!$this->config['socket']) {
            $this->pdo = new PDO("{$_ENV['DB_CONNECTION']}:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']};dbname={$_ENV['DB_DATABASE']};charset={$_ENV['DB_CHARSET']}", "{$_ENV['DB_USERNAME']}", "{$_ENV['DB_PASSWORD']}");
        } else {
            $this->pdo = new PDO("{$_ENV['DB_CONNECTION']}:unix_socket={$_ENV['DB_SOCKET']};dbname={$_ENV['DB_DATABASE']};charset={$_ENV['DB_CHARSET']}", "{$_ENV['DB_USERNAME']}", "{$_ENV['DB_PASSWORD']}");
        }

        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $this->pdo->setAttribute(PDO::ATTR_PERSISTENT, false);

        parent::__construct($this->pdo);
    }
}
