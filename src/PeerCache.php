<?php

declare(strict_types = 1);

namespace Pu239;

use MatthiasMullie\Scrapbook\Adapters\Collections\Utils\PrefixKeys;
use MatthiasMullie\Scrapbook\Adapters\Memcached;
use MatthiasMullie\Scrapbook\Buffered\BufferedStore;
use MatthiasMullie\Scrapbook\Buffered\TransactionalStore;
use MatthiasMullie\Scrapbook\Exception\UnbegunTransaction;
use Psr\Container\ContainerInterface;

/**
 * Class PeerCache.
 */
class PeerCache extends TransactionalStore
{
    protected $cache;
    protected $container;
    protected $env;

    /**
     * Cache constructor.
     *
     * @param ContainerInterface $c
     *
     */
    public function __construct(ContainerInterface $c)
    {
        $this->container = $c;
        $this->env = $this->container->get('env');
        if (!extension_loaded('memcached')) {
            die('<h1>Error</h1><p>php-memcached is not available</p>');
        }
        $client = $this->container->get(Memcached::class);
        $client = new PrefixKeys($client, $this->env['peer_cache']['prefix']);
        $client = new BufferedStore($client);
        $this->cache = new TransactionalStore($client);

        parent::__construct($this->cache);
    }
}
