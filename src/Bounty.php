<?php

declare(strict_types = 1);

namespace Pu239;

use Envms\FluentPDO\Exception;
use Envms\FluentPDO\Literal;
use PDOStatement;

/**
 * Class Bounty.
 */
class Bounty
{
    protected $fluent;
    protected $cache;
    protected $site_config;
    protected $settings;

    /**
     * Upcoming constructor.
     *
     * @param Cache    $cache
     * @param Database $fluent
     * @param Settings $settings
     *
     * @throws Exception
     */
    public function __construct(Cache $cache, Database $fluent, Settings $settings)
    {
        $this->settings = $settings;
        $this->site_config = $this->settings->get_settings();
        $this->fluent = $fluent;
        $this->cache = $cache;
    }

    /**
     * @param array $values
     *
     * @return bool|int
     */
    public function add(array $values)
    {
        try {
            $id = $this->fluent->insertInto('bounties')
                               ->values($values)
                               ->execute();

            return $id;
        } catch (\Exception $e) {
            $error = $e->getMessage();
            if (strpos($error, '1062') !== false) {
                $update = [
                    'amount' => new Literal('amount + ' . $values['amount']),
                    'updated' => new Literal('NOW()'),
                ];

                return $this->update($update, $values['userid'], $values['requestid']);
            }
            dd($e->getMessage());
        }

        return false;
    }

    /**
     * @param array $update
     * @param int   $userid
     * @param int   $requestid
     *
     * @return bool|int|PDOStatement|string
     */
    public function update(array $update, int $userid, int $requestid)
    {
        try {
            $id = $this->fluent->update('bounties')
                               ->set($update)
                               ->where('userid = ?', $userid)
                               ->where('requestid = ?', $requestid)
                               ->execute();

            return $id;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param array $update
     * @param int   $requestid
     *
     * @return bool|int|PDOStatement|string
     */
    public function pay(array $update, int $requestid)
    {
        try {
            $id = $this->fluent->update('bounties')
                               ->set($update)
                               ->where('requestid = ?', $requestid)
                               ->execute();

            return $id;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param int $requestid
     *
     * @return mixed|string
     */
    public function get_sum(int $requestid)
    {
        try {
            return $this->fluent->from('bounties')
                                ->select(null)
                                ->select('SUM(amount) AS bounty')
                                ->where('requestid = ?', $requestid)
                                ->fetch('bounty');
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     *
     * @param int $requestid
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function get_bounties(int $requestid)
    {
        $bounties = $this->fluent->from('bounties')
                                 ->select(null)
                                 ->select('userid')
                                 ->select('SUM(amount) AS amount')
                                 ->where('requestid = ?', $requestid)
                                 ->orderBy('amount DESC')
                                 ->groupBy('userid')
                                 ->fetchAll();

        return $bounties;
    }
}
