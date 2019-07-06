<?php

declare(strict_types = 1);

namespace Pu239;

use Envms\FluentPDO\Exception;

/**
 * Class Userblock.
 * @package Pu239
 */
class Userblock
{
    protected $fluent;
    protected $cache;
    protected $site_config;
    protected $settings;

    /**
     * Userblock constructor.
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
     * @param int $userid
     *
     * @throws Exception
     *
     * @return bool|mixed
     */
    public function get(int $userid)
    {
        $blocks = $this->cache->get('userblocks_' . $userid);
        if ($blocks === false || is_null($blocks)) {
            while (!$blocks) {
                $blocks = $this->fluent->from('user_blocks')
                                       ->select(null)
                                       ->select('index_page')
                                       ->select('global_stdhead')
                                       ->select('userdetails_page')
                                       ->where('userid = ?', $userid)
                                       ->fetch();
                if (!$blocks) {
                    $this->add(['userid' => $userid]);
                }
            }

            $this->cache->set('userblocks_' . $userid, $blocks, $this->site_config['expires']['u_status']);
        }

        return $blocks;
    }

    /**
     * @param array $values
     *
     * @throws Exception
     */
    public function add(array $values)
    {
        $this->fluent->insertInto('user_blocks')
                     ->values($values)
                     ->ignore()
                     ->execute();
    }
}
