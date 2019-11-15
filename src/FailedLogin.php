<?php

declare(strict_types = 1);

namespace Pu239;

use Envms\FluentPDO\Exception;
use Psr\Container\ContainerInterface;

/**
 * Class FailedLogin.
 */
class FailedLogin
{
    protected $cache;
    protected $fluent;
    protected $container;

    /**
     * FailedLogin constructor.
     *
     * @param Cache              $cache
     * @param Database           $fluent
     * @param ContainerInterface $c
     */
    public function __construct(Cache $cache, Database $fluent, ContainerInterface $c)
    {
        $this->container = $c;
        $this->fluent = $fluent;
        $this->cache = $cache;
    }

    /**
     *
     * @param string $ip
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function get(string $ip)
    {
        $fails = $this->fluent->from('failedlogins')
                              ->select(null)
                              ->select('SUM(attempts) AS attempts')
                              ->where('INET6_NTOA(ip) = ?', $ip)
                              ->fetch('attempts');

        return $fails;
    }

    /**
     * @param array  $set
     * @param string $ip
     *
     * @throws \Exception
     */
    public function set(array $set, string $ip)
    {
        $this->fluent->update('failedlogins')
                     ->set($set)
                     ->where('INET6_NTOA(ip) = ?', $ip)
                     ->execute();
    }

    /**
     * @param array $values
     * @param array $update
     *
     * @throws Exception
     */
    public function insert(array $values, array $update)
    {
        $this->fluent->insertInto('failedlogins', $values)
                     ->onDuplicateKeyUpdate($update)
                     ->execute();
    }

    /**
     * @param string $ip
     *
     * @throws Exception
     */
    public function delete(string $ip)
    {
        $this->fluent->deleteFrom('failedlogins')
                     ->where('INET6_NTOA(ip) = ?', $ip)
                     ->execute();
    }
}
