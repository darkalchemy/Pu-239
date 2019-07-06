<?php

declare(strict_types = 1);

namespace Pu239;

use Envms\FluentPDO\Exception;
use Psr\Container\ContainerInterface;

/**
 * Class Block.
 * @package Pu239
 */
class Block
{
    protected $fluent;
    protected $cache;
    protected $env;
    protected $container;

    /**
     * Block constructor.
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
    }

    /**
     * @param int $userid
     *
     * @throws Exception
     */
    public function get(int $userid)
    {
        $blocks = $this->cache->get('blocks_' . $userid);
        if ($blocks === false || is_null($blocks)) {
            $blocks = $this->fluent->from('blocks')
                                   ->where('userid = ?', $userid)
                                   ->fetch();

            $this->cache->set('blocks_' . $userid, $blocks, $this->env['expires']['user_blocks']);
        }
    }
}
