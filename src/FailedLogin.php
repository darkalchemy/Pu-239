<?php

namespace DarkAlchemy\Pu239;

class FailedLogin
{
    protected $cache;
    protected $fluent;
    protected $site_config;

    public function __construct()
    {
        global $fluent, $cache, $site_config;

        $this->fluent = $fluent;
        $this->cache = $cache;
        $this->site_config = $site_config;
    }

    /**
     * @param string $ip
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
     */
    public function insert(array $values, array $update)
    {
        $this->fluent->insertInto('failedlogins', $values)
            ->onDuplicateKeyUpdate($update)
            ->execute();
    }

    /**
     * @param string $ip
     */
    public function delete(string $ip)
    {
        $this->fluent->deleteFrom('failedlogins')
            ->where('INET6_NTOA(ip) = ?', $ip)
            ->execute();
    }
}
