<?php

declare(strict_types = 1);

namespace Pu239;

use Envms\FluentPDO\Exception;

/**
 * Class Casino.
 * @package Pu239
 */
class Casino
{
    protected $fluent;

    /**
     * Casino constructor.
     *
     * @param Database $fluent
     */
    public function __construct(Database $fluent)
    {
        $this->fluent = $fluent;
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
     * @throws Exception
     *
     * @return array|bool
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
     * @throws Exception
     *
     * @return array|bool
     */
    public function get_user(int $userid)
    {
        $user = $this->fluent->from('casino')
                             ->where('userid = ?', $userid)
                             ->fetch();

        return $user;
    }

    /**
     * @throws Exception
     *
     * @return mixed
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
