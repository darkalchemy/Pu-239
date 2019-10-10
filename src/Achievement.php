<?php

declare(strict_types = 1);

namespace Pu239;

use Envms\FluentPDO\Exception;
use Psr\Container\ContainerInterface;

/**
 * Class Achievement.
 */
class Achievement
{
    protected $cache;
    protected $fluent;
    protected $env;
    protected $limit;
    protected $container;

    /**
     * Achievement constructor.
     *
     * @param Cache              $cache
     * @param Database           $fluent
     * @param ContainerInterface $c
     */
    public function __construct(Cache $cache, Database $fluent, ContainerInterface $c)
    {
        $this->container = $c;
        $this->env = $this->container->get('env');
        $this->fluent = $fluent;
        $this->cache = $cache;
        $this->limit = $this->env['db']['query_limit'];
    }

    /**
     * @param array $values
     * @param array $update
     *
     * @throws Exception
     */
    public function insert(array $values, array $update)
    {
        $count = (int) ($this->limit / max(array_map('count', $values)));
        foreach (array_chunk($values, $count) as $t) {
            $this->fluent->insertInto('achievements', $t)
                         ->onDuplicateKeyUpdate($update)
                         ->execute();
        }
    }

    /**
     * @param int $userid
     *
     * @return mixed|string
     */
    public function get_achievements_count(int $userid)
    {
        try {
            return $this->fluent->from('achievements')
                                ->select(null)
                                ->select('COUNT(id) AS count')
                                ->where('userid = ?', $userid)
                                ->fetch('count');
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param int $userid
     * @param int $limit
     * @param int $offset
     *
     * @return string
     */
    public function get_achievements(int $userid, int $limit, int $offset)
    {
        try {
            return $this->fluent->from('achievements')
                                ->where('userid = ?', $userid)
                                ->orderBy('date DESC')
                                ->limit($limit)
                                ->offset($offset);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
