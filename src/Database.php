<?php

declare(strict_types = 1);

namespace Pu239;

use Envms\FluentPDO\Query;
use PDO;
use Psr\Container\ContainerInterface;

/**
 * Class Database.
 */
class Database extends Query
{
    protected $pdo;
    protected $container;

    /**
     * Database constructor.
     *
     * @param ContainerInterface $c
     * @param PDO                $pdo
     */
    public function __construct(ContainerInterface $c, PDO $pdo)
    {
        $this->container = $c;
        $this->pdo = $pdo;
        parent::__construct($this->pdo);
    }
}
