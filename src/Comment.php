<?php

declare(strict_types = 1);

namespace Pu239;

use Envms\FluentPDO\Exception;
use Envms\FluentPDO\Queries\Select;
use PDOStatement;
use Psr\Container\ContainerInterface;

/**
 * Class Comment.
 */
class Comment
{
    protected $cache;
    protected $fluent;
    protected $env;
    protected $image;
    protected $container;
    protected $site_config;

    /**
     * Comment constructor.
     *
     * @param Cache              $cache
     * @param Database           $fluent
     * @param Image              $image
     * @param Settings           $settings
     * @param ContainerInterface $c
     *
     * @throws Exception
     */
    public function __construct(Cache $cache, Database $fluent, Image $image, Settings $settings, ContainerInterface $c)
    {
        $this->container = $c;
        $this->env = $this->container->get('env');
        $this->site_config = $settings->get_settings();
        $this->fluent = $fluent;
        $this->image = $image;
        $this->cache = $cache;
    }

    /**
     * @param int $tid
     * @param int $count
     * @param int $perpage
     *
     * @throws Exception
     *
     * @return array
     */
    public function get_torrent_comment(int $tid, int $count, int $perpage)
    {
        require_once INCL_DIR . 'function_pager.php';
        $pager = pager($perpage, $count, $this->env['paths']['baseurl'] . "/details.php?id=$tid&amp;", [
            'lastpagedefault' => 1,
        ]);
        $comments = $this->fluent->from('comments')
                                 ->where('torrent = ?', $tid)
                                 ->orderBy('id DESC')
                                 ->limit($pager['pdo']['limit'])
                                 ->offset($pager['pdo']['offset'])
                                 ->fetchAll();

        return [
            $comments,
            $pager,
        ];
    }

    /**
     * @throws Exception
     *
     * @return array|bool|mixed
     */
    public function get_comments()
    {
        $comments = $this->cache->get('latest_comments_');
        if ($comments === false || is_null($comments)) {
            $comments = [];
            $torrents = $this->fluent->from('comments AS c')
                                     ->select(null)
                                     ->select('c.id AS comment_id')
                                     ->select('c.user')
                                     ->select('c.torrent AS id')
                                     ->select('c.added')
                                     ->select('c.text')
                                     ->select('c.anonymous')
                                     ->select('c.user_likes')
                                     ->select('t.id')
                                     ->select('t.added')
                                     ->select('t.seeders')
                                     ->select('t.leechers')
                                     ->select('t.name')
                                     ->select('t.size')
                                     ->select('t.poster')
                                     ->select('t.anonymous')
                                     ->select('t.owner')
                                     ->select('t.imdb_id')
                                     ->select('t.times_completed')
                                     ->select('t.rating')
                                     ->select('t.year')
                                     ->select('t.subs AS subtitles')
                                     ->select('t.audios')
                                     ->select('t.newgenre AS genre')
                                     ->select('u.username')
                                     ->select('u.class')
                                     ->select('p.name AS parent_name')
                                     ->select('s.name AS cat')
                                     ->select('s.image')
                                     ->innerJoin('torrents AS t ON t.id=c.torrent')
                                     ->leftJoin('users AS u ON u.id = c.user')
                                     ->leftJoin('categories AS s ON t.category = s.id')
                                     ->leftJoin('categories AS p ON s.parent_id = p.id')
                                     ->where('c.torrent > 0')
                                     ->orderBy('c.id DESC')
                                     ->limit(5);

            foreach ($torrents as $torrent) {
                if (!empty($torrent['parent_name'])) {
                    $torrent['cat'] = $torrent['parent_name'] . '::' . $torrent['cat'];
                }
                $comments[] = $torrent;
            }
            $this->cache->set('latest_comments_', $comments, $this->site_config['expires']['latestcomments']);
        }
        foreach ($comments as $comment) {
            if (empty($comment['poster']) && !empty($comment['imdb_id'])) {
                $this->image->find_images($comment['imdb_id']);
            }
        }

        return $comments;
    }

    /**
     * @param int $id
     *
     * @throws Exception
     *
     * @return bool
     */
    public function delete(int $id)
    {
        $result = $this->fluent->deleteFrom('comments')
                               ->where('id = ?', $id)
                               ->execute();

        $this->cache->delete('latest_comments_');

        return $result;
    }

    /**
     * @param array $set
     * @param int   $id
     *
     * @throws Exception
     *
     * @return bool|int|PDOStatement
     */
    public function update(array $set, int $id)
    {
        $result = $this->fluent->update('comments')
                               ->set($set)
                               ->where('id = ?', $id)
                               ->execute();

        $this->cache->delete('latest_comments_');

        return $result;
    }

    /**
     * @param array $values
     *
     * @throws Exception
     *
     * @return bool|int
     */
    public function add(array $values)
    {
        $id = $this->fluent->insertInto('comments')
                           ->values($values)
                           ->execute();

        return $id;
    }

    /**
     * @param string $column
     * @param int    $id
     *
     * @throws Exception
     *
     * @return Select
     */
    public function get_comment_by_column(string $column, int $id)
    {
        $comments = $this->fluent->from('comments');
        if ($column === 'request') {
            $comments = $comments->where('request = ?', $id);
        } elseif ($column === 'offer') {
            $comments = $comments->where('offer = ?', $id);
        } elseif ($column === 'recipe') {
            $comments = $comments->where('recipe = ?', $id);
        }
        $comments = $comments->orderBy('id DESC')
                             ->fetchAll();

        return $comments;
    }

    /**
     * @param int $commentid
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function get_comment_by_id(int $commentid)
    {
        $comment = $this->fluent->from('comments')
                                ->where('id = ?', $commentid)
                                ->fetch();

        return $comment;
    }
}
