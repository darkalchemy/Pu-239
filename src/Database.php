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
    protected $site_config;

    public function __construct()
    {
        global $site_config;

        $this->site_config = $site_config;
        if (!$this->site_config['database']['use_socket']) {
            $this->pdo = new PDO("{$this->site_config['database']['type']}:host={$this->site_config['database']['host']};port={$this->site_config['database']['port']};dbname={$this->site_config['database']['database']};charset=utf8mb4", "{$this->site_config['database']['username']}", "{$this->site_config['database']['password']}");
        } else {
            $this->pdo = new PDO("{$this->site_config['database']['type']}:unix_socket={$this->site_config['database']['socket']};dbname={$this->site_config['database']['database']};charset=utf8mb4", "{$this->site_config['database']['username']}", "{$this->site_config['database']['password']}");
        }

        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $this->pdo->setAttribute(PDO::ATTR_PERSISTENT, false);

        parent::__construct($this->pdo);
    }
}
