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
        switch ($_ENV['CACHE_DRIVER']) {
            case 'couchbase':
                $cluster = new \CouchbaseCluster('couchbase://localhost');
                $bucket = $cluster->openBucket('default');
                $cache = new \MatthiasMullie\Scrapbook\Adapters\Couchbase($bucket);
                break;

            case 'apcu':
                if (extension_loaded('apcu')) {
                    $cache = new \MatthiasMullie\Scrapbook\Adapters\Apc();
                } else {
                    die('<h1>Error</h1><p>php-apcu is not available</p>');
                }

                break;

            case 'memcached':
                if (extension_loaded('memcached')) {
                    $client = new \Memcached();
                    $client->addServer($site_config['memcached_host'], $site_config['memcached_port']);
                    $cache = new \MatthiasMullie\Scrapbook\Adapters\Memcached($client);
                } else {
                    die('<h1>Error</h1><p>php-memcached is not available</p>');
                }

                break;

            case 'redis':
                if (extension_loaded('redis')) {
                    $client = new \Redis();
                    $client->connect($site_config['redis_host']);
                    $cache = new \MatthiasMullie\Scrapbook\Adapters\Redis($client);
                } else {
                    die('<h1>Error</h1><p>php-redis is not available</p>');
                }
                break;

            default:
                $adapter = new \League\Flysystem\Adapter\Local($_ENV['FILES_PATH'], LOCK_EX);
                $filesystem = new \League\Flysystem\Filesystem($adapter);
                $cache = new \MatthiasMullie\Scrapbook\Adapters\Flysystem($filesystem);
        }
        $cache = new \MatthiasMullie\Scrapbook\Adapters\Collections\Utils\PrefixKeys($cache, $site_config['cookie_prefix']);
        $cache = new \MatthiasMullie\Scrapbook\Buffered\BufferedStore($cache);
        $cache = new \MatthiasMullie\Scrapbook\Buffered\TransactionalStore($cache);

        parent::__construct($cache);
    }

    /**
     * @param     $key
     * @param     $set
     * @param int $ttl
     *
     * @throws \MatthiasMullie\Scrapbook\Exception\UnbegunTransaction
     */
    public function update_row($key, $set, $ttl = 0)
    {
        $this->begin();
        $array = $this->get($key);
        if (!empty($array)) {
            $array = array_replace($array, $set);
            $this->set($key, $array, $ttl);
        }
        $this->commit();
    }
}
