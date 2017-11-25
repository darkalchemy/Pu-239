<?php

/**
 * Class CACHE
 */
class CACHE extends \MatthiasMullie\Scrapbook\Buffered\TransactionalStore
{
    /**
     * CACHE constructor.
     */
    public function __construct()
    {
        global $site_config;
        switch ($site_config['cache_adapter']) {
            case 'couchbase':
                $cluster = new \CouchbaseCluster('couchbase://localhost');
                $bucket = $cluster->openBucket('default');
                $cache = new \MatthiasMullie\Scrapbook\Adapters\Couchbase($bucket);
                break;
            case 'apcu':
                $cache = new \MatthiasMullie\Scrapbook\Adapters\Apc();
                break;
            case 'memcached':
                $client = new \Memcached();
                $client->addServer($site_config['memcached_host'], $site_config['memcached_port']);
                $cache = new \MatthiasMullie\Scrapbook\Adapters\Memcached($client);
                break;
            case 'redis':
                $client = new \Redis();
                $client->connect($site_config['redis_host']);
                $cache = new \MatthiasMullie\Scrapbook\Adapters\Redis($client);
                break;
            default:
                $adapter = new \League\Flysystem\Adapter\Local($site_config['filesystem_path'], LOCK_EX);
                $filesystem = new \League\Flysystem\Filesystem($adapter);
                $cache = new \MatthiasMullie\Scrapbook\Adapters\Flysystem($filesystem);
        }
        $cache = new \MatthiasMullie\Scrapbook\Adapters\Collections\Utils\PrefixKeys($cache, $site_config['cookie_prefix']);
        $cache = new \MatthiasMullie\Scrapbook\Buffered\BufferedStore($cache);
        $cache = new \MatthiasMullie\Scrapbook\Buffered\TransactionalStore($cache);
        $cache = new \MatthiasMullie\Scrapbook\Scale\StampedeProtector($cache);

        parent::__construct($cache);
    }

    /**
     * @param     $key
     * @param     $set
     * @param int $ttl
     */
    public function update_row($key, $set, $ttl = 0)
    {
        $this->begin();
        $array = $this->get($key);
        $array = array_replace($array, $set);
        $this->set($key, $array, $ttl);
        $this->commit();
    }
}
