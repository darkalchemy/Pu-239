<?php declare(strict_types = 1);

namespace Pu239;

use Envms\FluentPDO\Exception;
use Psr\Container\ContainerInterface;

class Casino
{
    protected $cache;
    protected $fluent;
    protected $container;

    /**
     * Casino constructor.
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
     * @param int $userid
     *
     * @throws Exception
     */
    public function reset_trys(int $userid)
    {
        $set = ['trys' => 0];
        $this->fluent->update('casino')
                     ->set($set)
                     ->where('date < ?', TIME_NOW)
                     ->where('trys >= 51')
                     ->where('enableplay = "yes"')
                     ->where('userid = ?', $userid)
                     ->execute();

    }

    /**
     * @param int $userid
     *
     * @return array|bool
     * @throws Exception
     */
    public function add_user(int $userid)
    {
        $values = [
            'userid' => $userid,
            'date' => TIME_NOW,
        ];
        $this->fluent->insertInto('casino')
                     ->values($values)
                     ->execute();

        return $this->get_user($userid);
    }

    /**
     * @param int $userid
     *
     * @return array|bool
     * @throws Exception
     */
    public function get_user(int $userid)
    {
        $user = $this->fluent->from('casino')
                             ->where('userid = ?', $userid)
                             ->fetch();

        return $user;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function get_totals()
    {
        $result = $this->fluent->from('casino')
                               ->select(null)
                               ->select('SUM(win) - SUM(lost) AS globaldown')
                               ->select('SUM(deposit) AS globaldeposit')
                               ->select('SUM(win) AS win')
                               ->select('SUM(lost) AS lost')
                               ->fetch();

        return $result;
    }

    /**
     * @param array $set
     * @param int   $userid
     *
     * @throws Exception
     */
    public function update_user(array $set, int $userid)
    {
        $this->fluent->update('casino')
            ->set($set)
            ->where('userid = ?', $userid)
            ->execute();
    }
}