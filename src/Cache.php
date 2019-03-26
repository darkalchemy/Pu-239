<?php

namespace Pu239;

use MatthiasMullie\Scrapbook\Adapters\Couchbase;
use MatthiasMullie\Scrapbook\Adapters\Apc;
use MatthiasMullie\Scrapbook\Adapters\Memcached;
use MatthiasMullie\Scrapbook\Adapters\Redis;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use MatthiasMullie\Scrapbook\Adapters\Flysystem;
use MatthiasMullie\Scrapbook\Buffered\BufferedStore;
use MatthiasMullie\Scrapbook\Buffered\TransactionalStore;
use MatthiasMullie\Scrapbook\Adapters\Collections\Utils\PrefixKeys;

/**
 * Class Cache.
 */
class Cache extends TransactionalStore
{
    protected $cache;
    protected $site_config;

    /**
     * Cache constructor.
     *
     * @throws \MatthiasMullie\Scrapbook\Exception\Exception
     * @throws \MatthiasMullie\Scrapbook\Exception\ServerUnhealthy
     */
    public function __construct()
    {
        global $site_config;

        $this->site_config = $site_config;

        switch ($_ENV['CACHE_DRIVER']) {
            case 'couchbase':
                $cluster = new \CouchbaseCluster('couchbase://localhost');
                $bucket = $cluster->openBucket('default');
                $this->cache = new Couchbase($bucket);
                break;

            case 'apcu':
                if (extension_loaded('apcu')) {
                    $this->cache = new Apc();
                } else {
                    die('<h1>Error</h1><p>php-apcu is not available</p>');
                }

                break;

            case 'memcached':
                if (extension_loaded('memcached')) {
                    $client = new \Memcached();
                    if (!count($client->getServerList())) {
                        if (!$this->site_config['socket']) {
                            $client->addServer($_ENV['MEMCACHED_HOST'], $_ENV['MEMCACHED_PORT']);
                        } else {
                            $client->addServer($_ENV['MEMCACHED_SOCKET'], 0);
                        }
                    }
                    $this->cache = new Memcached($client);
                } else {
                    die('<h1>Error</h1><p>php-memcached is not available</p>');
                }

                break;

            case 'redis':
                if (extension_loaded('redis')) {
                    $client = new \Redis();
                    if (!$this->site_config['socket']) {
                        $client->connect($_ENV['REDIS_HOST'], $_ENV['REDIS_PORT']);
                    } else {
                        $client->connect($_ENV['REDIS_SOCKET']);
                    }

                    $client->select($_ENV['REDIS_DATABASE']);
                    $this->cache = new Redis($client);
                } else {
                    die('<h1>Error</h1><p>php-redis is not available</p>');
                }
                break;

            default:
                $adapter = new Local($_ENV['FILES_PATH'], LOCK_EX);
                $filesystem = new Filesystem($adapter);
                $this->cache = new Flysystem($filesystem);
        }
        $this->cache = new PrefixKeys($this->cache, $_ENV['CACHE_PREFIX']);
        $this->cache = new BufferedStore($this->cache);
        $this->cache = new TransactionalStore($this->cache);

        parent::__construct($this->cache);
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

    /**
     * @return bool
     */
    public function flushDB()
    {
        if ($_ENV['CACHE_DRIVER'] === 'redis') {
            $client = new \Redis();
            if (!$this->site_config['socket']) {
                $client->connect($_ENV['REDIS_HOST'], $_ENV['REDIS_PORT']);
            } else {
                $client->connect($_ENV['REDIS_SOCKET']);
            }
            $client->select($_ENV['REDIS_DATABASE']);

            return $client->flushDB();
        } else {
            return $this->flush();
        }
    }
}
