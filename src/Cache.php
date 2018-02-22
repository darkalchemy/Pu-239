<?php

namespace DarkAlchemy\Pu239;

use MatthiasMullie\Scrapbook\Buffered\TransactionalStore;

class Cache extends TransactionalStore
{

    /**
     * Cache constructor.
     *
     * @throws \MatthiasMullie\Scrapbook\Exception\Exception
     * @throws \MatthiasMullie\Scrapbook\Exception\ServerUnhealthy
     */
    public function __construct()
    {
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
                    if (!count($client->getServerList())) {
                        $client->addServer($_ENV['MEMCACHED_HOST'], $_ENV['MEMCACHED_PORT']);
                    }
                    $cache = new \MatthiasMullie\Scrapbook\Adapters\Memcached($client);
                } else {
                    die('<h1>Error</h1><p>php-memcached is not available</p>');
                }

                break;

            case 'redis':
                if (extension_loaded('redis')) {
                    $client = new \Redis();
                    $client->connect($_ENV['REDIS_HOST'], $_ENV['REDIS_PORT']);
                    $client->select($_ENV['REDIS_DATABASE']);
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
        $cache = new \MatthiasMullie\Scrapbook\Adapters\Collections\Utils\PrefixKeys($cache, $_ENV['CACHE_PREFIX']);
        $cache = new \MatthiasMullie\Scrapbook\Buffered\BufferedStore($cache);
        $cache = new TransactionalStore($cache);

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
