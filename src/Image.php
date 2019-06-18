<?php

declare(strict_types = 1);

namespace Pu239;

use Envms\FluentPDO\Exception;
use Envms\FluentPDO\Literal;
use Envms\FluentPDO\Queries\Select;
use Psr\Container\ContainerInterface;

/**
 * Class Image.
 */
class Image
{
    protected $fluent;
    protected $env;
    protected $limit;
    protected $container;
    protected $cache;

    /**
     * Image constructor.
     *
     * @param Database           $fluent
     * @param Cache              $cache
     * @param ContainerInterface $c
     */
    public function __construct(Database $fluent, Cache $cache, ContainerInterface $c)
    {
        $this->container = $c;
        $this->env = $this->container->get('env');
        $this->fluent = $fluent;
        $this->cache = $cache;
        $this->limit = $this->env['db']['query_limit'];
    }

    /**
     * @param array $values
     *
     * @throws \Exception
     */
    public function insert(array $values)
    {
        $this->fluent->insertInto('images')
                     ->values($values)
                     ->ignore()
                     ->execute();
    }

    /**
     * @param array $values
     *
     * @throws Exception
     */
    public function insert_update(array $values)
    {
        $update = [
            'imdb_id' => new Literal('VALUES(imdb_id)'),
            'tmdb_id' => new Literal('VALUES(tmdb_id)'),
            'type' => new Literal('VALUES(type)'),
        ];
        $count = (int) ($this->limit / max(array_map('count', $values)));
        foreach (array_chunk($values, $count) as $t) {
            $this->fluent->insertInto('images', $t)
                         ->onDuplicateKeyUpdate($update)
                         ->execute();
        }
    }

    /**
     * @param array $values
     * @param array $update
     *
     * @throws Exception
     */
    public function update(array $values, array $update)
    {
        $count = (int) ($this->limit / max(array_map('count', $values)));
        foreach (array_chunk($values, $count) as $t) {
            $this->fluent->insertInto('images', $t)
                         ->onDuplicateKeyUpdate($update)
                         ->execute();
        }
    }

    /**
     * @param string $imdb
     * @param string $type
     *
     * @throws Exception
     *
     * @return string|null
     */
    public function find_images(string $imdb, string $type = 'poster')
    {
        $images = $this->cache->get($type . '_' . $imdb);
        if ($images === false || is_null($images)) {
            $images = $this->fluent->from('images')
                                   ->select(null)
                                   ->select('url')
                                   ->where('type = ?', $type)
                                   ->where('imdb_id = ?', $imdb)
                                   ->fetchAll();

            if (!empty($images)) {
                $this->cache->set($type . '_' . $imdb, $images, 86400);
            } else {
                $this->cache->set($type . '_' . $imdb, [], 3600);
            }
        }

        if (!empty($images)) {
            shuffle($images);
            $image = $images[0]['url'];

            return $image;
        }

        return null;
    }

    /**
     * @param int $id
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function get_image(int $id)
    {
        return $this->fluent->from('images')
                            ->where('id = ?', $id)
                            ->fetch();
    }

    /**
     * @param int $limit
     * @param int $offset
     *
     * @throws Exception
     *
     * @return array|bool
     */
    public function get_images(int $limit, int $offset)
    {
        return $this->fluent->from('images')
                            ->limit($limit)
                            ->offset($offset)
                            ->fetchAll();
    }

    /**
     * @throws Exception
     *
     * @return mixed
     */
    public function get_image_count()
    {
        return $this->fluent->from('images')
                            ->select(null)
                            ->select('COUNT(id) AS count')
                            ->fetch('count');
    }

    /**
     * @param int $id
     *
     * @throws Exception
     */
    public function delete_image(int $id)
    {
        $this->fluent->deleteFrom('images')
                     ->where('id = ?', $id)
                     ->execute();
    }

    /**
     * @param string $terms
     *
     * @throws Exception
     *
     * @return Select|mixed
     */
    public function count_search_images(string $terms)
    {
        $count = $this->fluent->from('images')
                              ->select(null)
                              ->select('COUNT(id) AS count');
        $terms = explode(' ', trim($terms));
        foreach ($terms as $term) {
            $term = trim($term);
            if (in_array($term, [
                'poster',
                'banner',
                'background',
            ])) {
                $count = $count->where('type = :type', [':type' => $term]);
            } else {
                $count = $count->where('(imdb_id = :imdb OR tmdb_id = :tmdb OR tvmaze_id = :tvmaze OR isbn = :isbn)', [
                    ':imdb' => $term,
                    ':tmdb' => $term,
                    ':tvmaze' => $term,
                    ':isbn' => $term,
                ]);
            }
        }
        $count = $count->fetch('count');

        return $count;
    }

    /**
     * @param string $terms
     * @param int    $limit
     * @param int    $offset
     *
     * @throws Exception
     *
     * @return array|bool|Select
     */
    public function search_images(string $terms, int $limit, int $offset)
    {
        $query = $this->fluent->from('images');
        $terms = explode(' ', trim($terms));
        foreach ($terms as $term) {
            $term = trim($term);
            if (in_array($term, [
                'poster',
                'banner',
                'background',
            ])) {
                $query = $query->where('type = :type', [':type' => $term]);
            } else {
                $query = $query->where('(imdb_id = :imdb OR tmdb_id = :tmdb OR tvmaze_id = :tvmaze OR isbn = :isbn)', [
                    ':imdb' => $term,
                    ':tmdb' => $term,
                    ':tvmaze' => $term,
                    ':isbn' => $term,
                ]);
            }
        }
        $query = $query->limit($limit)
                       ->offset($offset)
                       ->fetchAll();

        return $query;
    }
}
