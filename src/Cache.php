<?php

namespace Pu239;

use CouchbaseCluster;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use MatthiasMullie\Scrapbook\Adapters\Apc;
use MatthiasMullie\Scrapbook\Adapters\Collections\Utils\PrefixKeys;
use MatthiasMullie\Scrapbook\Adapters\Couchbase;
use MatthiasMullie\Scrapbook\Adapters\Flysystem;
use MatthiasMullie\Scrapbook\Adapters\Memcached;
use MatthiasMullie\Scrapbook\Adapters\Redis;
use MatthiasMullie\Scrapbook\Buffered\BufferedStore;
use MatthiasMullie\Scrapbook\Buffered\TransactionalStore;
use MatthiasMullie\Scrapbook\Exception\Exception;
use MatthiasMullie\Scrapbook\Exception\ServerUnhealthy;
use MatthiasMullie\Scrapbook\Exception\UnbegunTransaction;

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
     * @throws Exception
     * @throws ServerUnhealthy
     */
    public function __construct()
    {
        global $site_config;

        $this->site_config = $site_config;

        switch ($this->site_config['cache']['driver']) {
            case 'couchbase':
                $cluster = new CouchbaseCluster('couchbase://localhost');
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
                        if (!$this->site_config['memcached']['use_socket']) {
                            $client->addServer($this->site_config['memcached']['host'], $this->site_config['memcached']['port']);
                        } else {
                            $client->addServer($this->site_config['memcached']['socket'], 0);
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
                    if (!$this->site_config['redis']['use_socket']) {
                        $client->connect($this->site_config['redis']['host'], $this->site_config['redis']['port']);
                    } else {
                        $client->connect($this->site_config['redis']['socket']);
                    }

                    $client->select($this->site_config['redis']['database']);
                    $this->cache = new Redis($client);
                } else {
                    die('<h1>Error</h1><p>php-redis is not available</p>');
                }
                break;

            default:
                $adapter = new Local($this->site_config['files']['path'], LOCK_EX);
                $filesystem = new Filesystem($adapter);
                $this->cache = new Flysystem($filesystem);
        }
        $this->cache = new PrefixKeys($this->cache, $this->site_config['cache']['prefix']);
        $this->cache = new BufferedStore($this->cache);
        $this->cache = new TransactionalStore($this->cache);

        parent::__construct($this->cache);
    }

    /**
     * @param     $key
     * @param     $set
     * @param int $ttl
     *
     * @throws UnbegunTransaction
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
        if ($this->site_config['cache']['driver'] === 'redis') {
            $client = new \Redis();
            if (!$this->site_config['redis']['use_socket']) {
                $client->connect($this->site_config['redis']['host'], $this->site_config['redis']['port']);
            } else {
                $client->connect($this->site_config['redis']['socket']);
            }
            $client->select($this->site_config['redis']['database']);

            return $client->flushDB();
        } else {
            return $this->flush();
        }
    }
}
