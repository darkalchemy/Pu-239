<?php

declare(strict_types = 1);

namespace Pu239;

use CouchbaseCluster;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use MatthiasMullie\Scrapbook\Adapters\Apc;
use MatthiasMullie\Scrapbook\Adapters\Collections\Utils\PrefixKeys;
use MatthiasMullie\Scrapbook\Adapters\Couchbase;
use MatthiasMullie\Scrapbook\Adapters\Flysystem;
use MatthiasMullie\Scrapbook\Adapters\Memcached;
use MatthiasMullie\Scrapbook\Adapters\MemoryStore;
use MatthiasMullie\Scrapbook\Adapters\Redis;
use MatthiasMullie\Scrapbook\Buffered\BufferedStore;
use MatthiasMullie\Scrapbook\Buffered\TransactionalStore;
use MatthiasMullie\Scrapbook\Exception\Exception;
use MatthiasMullie\Scrapbook\Exception\ServerUnhealthy;
use MatthiasMullie\Scrapbook\Exception\UnbegunTransaction;
use Psr\Container\ContainerInterface;

/**
 * Class Cache.
 */
class Cache extends TransactionalStore
{
    protected $cache;
    protected $container;
    protected $env;

    /**
     * Cache constructor.
     *
     * @param ContainerInterface $c
     *
     * @throws Exception
     * @throws ServerUnhealthy
     */
    public function __construct(ContainerInterface $c)
    {
        $this->container = $c;
        $this->env = $this->container->get('env');

        switch ($this->env['cache']['driver']) {
            case 'couchbase':
                if (!extension_loaded(couchbase)) {
                    die('<h1>Error</h1><p>php-couchbase is not available</p>');
                }
                $cluster = new CouchbaseCluster('couchbase://localhost');
                $bucket = $cluster->openBucket('default');
                $this->cache = new Couchbase($bucket);

                break;

            case 'apcu':
                if (!extension_loaded('apcu')) {
                    die('<h1>Error</h1><p>php-apcu is not available</p>');
                }
                $this->cache = new Apc();

                break;

            case 'memcached':
                if (!extension_loaded('memcached')) {
                    die('<h1>Error</h1><p>php-memcached is not available</p>');
                }
                $this->cache = $this->container->get(Memcached::class);

                break;

            case 'redis':
                if (!extension_loaded('redis')) {
                    die('<h1>Error</h1><p>php-redis is not available</p>');
                }
                $this->cache = $this->container->get(Redis::class);

                break;

            case 'memory':
                $this->cache = new MemoryStore();
                break;

            case 'file':
                if (!class_exists('Flysystem')) {
                    die('<h1>Error</h1><p>Class Flysystem is not available</p>');
                }

                $adapter = new Local($this->env['files']['path'], LOCK_EX);
                $filesystem = new Filesystem($adapter);
                $this->cache = new Flysystem($filesystem);
                break;

            default:
                die('Invalid Adaptor: ' . $this->env['cache']['driver'] . '<br>Valid choices: memory, file, apcu, memcached, redis, couchbase');
        }
        $this->cache = new PrefixKeys($this->cache, $this->env['cache']['prefix']);
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
        if (file_exists($this->env['files']['path'] . 'CompiledContainer.php')) {
            unlink($this->env['files']['path'] . 'CompiledContainer.php');
        }
        if ($this->env['cache']['driver'] === 'redis') {
            $client = new \Redis();
            if (!$this->env['redis']['use_socket']) {
                $client->connect($this->env['redis']['host'], $this->env['redis']['port']);
            } else {
                $client->connect($this->env['redis']['socket']);
            }
            $client->select((int) $this->env['redis']['database']);

            return $client->flushDB();
        } else {
            return $this->flush();
        }
    }
}
