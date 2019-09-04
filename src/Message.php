<?php

declare(strict_types = 1);

namespace Pu239;

use Envms\FluentPDO\Exception;
use Envms\FluentPDO\Queries\Select;
use PDOStatement;
use Psr\Container\ContainerInterface;

/**
 * Class Message.
 */
class Message
{
    protected $cache;
    protected $fluent;
    protected $env;
    protected $limit;
    protected $container;
    protected $site_config;
    protected $users;

    /**
     * Message constructor.
     *
     * @param Cache              $cache
     * @param Database           $fluent
     * @param Settings           $settings
     * @param User               $users
     * @param ContainerInterface $c
     *
     * @throws Exception
     */
    public function __construct(Cache $cache, Database $fluent, Settings $settings, User $users, ContainerInterface $c)
    {
        $this->container = $c;
        $this->env = $this->container->get('env');
        $this->site_config = $settings->get_settings();
        $this->fluent = $fluent;
        $this->cache = $cache;
        $this->users = $users;
        $this->limit = $this->env['db']['query_limit'];
    }

    public function insert(array $values, bool $send_email = true)
    {
        if (empty($values)) {
            return false;
        }
        $count = (int) ($this->limit / max(array_map('count', $values)));
        foreach (array_chunk($values, $count) as $t) {
            $result = $this->fluent->insertInto('messages')
                                   ->values($t)
                                   ->execute();
        }

        foreach ($values as $user) {
            $ids[] = 'inbox_' . $user['receiver'];
            $ids[] = 'message_count_' . $user['receiver'];
            if ($send_email && $this->site_config['mail']['smtp_enable']) {
                $emailer = $this->users->getUserFromId($user['receiver']);
                if (!empty($emailer['notifs']) && strpos($emailer['notifs'], 'email') !== false) {
                    $msg_body = format_comment($user['msg']);
                    send_mail(strip_tags($emailer['email']), $user['subject'], $msg_body, strip_tags($msg_body));
                }
            }
        }

        if (!empty($ids)) {
            $this->cache->deleteMulti($ids);
        }

        if (!empty($result)) {
            return $result;
        }

        return false;
    }

    /**
     * @param int $id
     * @param int $userid
     *
     * @throws Exception
     *
     * @return bool
     */
    public function delete(int $id, int $userid)
    {
        $result = $this->fluent->delete('messages')
                               ->where('id = ?', $id)
                               ->execute();

        $this->cache->decrement('inbox_' . $userid);
        $this->cache->decrement('message_count_' . $userid);

        return $result;
    }

    /**
     * @param int $id
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function get_by_id(int $id)
    {
        $message = $this->fluent->from('messages')
                                ->where('id = ?', $id)
                                ->fetch();

        return $message;
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
        $result = $this->fluent->update('messages')
                               ->set($set)
                               ->where('id = ?', $id)
                               ->execute();

        return $result;
    }

    /**
     * @param array $set
     * @param int   $location
     * @param int   $userid
     *
     * @throws Exception
     */
    public function update_location(array $set, int $location, int $userid)
    {
        $this->fluent->update('messages')
                     ->set($set)
                     ->where('location = ?', $location)
                     ->where('receiver = ?', $userid)
                     ->execute();
    }

    /**
     * @param int  $userid
     * @param int  $location
     * @param bool $unread
     *
     * @throws Exception
     *
     * @return int
     */
    public function get_count(int $userid, int $location, bool $unread)
    {
        $pmCount = false;
        if ($location === $this->site_config['pm']['inbox'] && $unread) {
            $pmCount = $this->cache->get('inbox_' . $userid);
        }
        if ($pmCount === false || is_null($pmCount)) {
            $pmCount = $this->fluent->from('messages')
                                    ->select(null)
                                    ->select('COUNT(id) AS count');
            if ($location === $this->site_config['pm']['sent']) {
                $pmCount = $pmCount->where('sender = ?', $userid)
                                   ->where('location = ?', $this->site_config['pm']['inbox']);
            } else {
                $pmCount = $pmCount->where('receiver = ?', $userid)
                                   ->where('location = ?', $location);
            }
            if ($unread) {
                $pmCount = $pmCount->where('unread = "yes"');
            }
            $pmCount = $pmCount->where('draft = "no"')
                               ->fetch('count');
            if ($location === $this->site_config['pm']['inbox'] && $unread) {
                $this->cache->set('inbox_' . $userid, $pmCount, $this->site_config['expires']['unread']);
            }
        }

        return is_int($pmCount) ? $pmCount : 0;
    }

    /**
     * @param int $userid
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function get_total_count(int $userid)
    {
        $pmCount = $this->cache->get('message_count_' . $userid);
        if ($pmCount === false || is_null($pmCount)) {
            $pmCount = $this->fluent->from('messages')
                                    ->select(null)
                                    ->select('COUNT(id) AS count')
                                    ->where('receiver = ?', $userid)
                                    ->fetch('count');

            $this->cache->set('message_count_' . $userid, $pmCount, $this->site_config['expires']['unread']);
        }

        return $pmCount;
    }

    /**
     * @param int $dt
     *
     * @throws Exception
     *
     * @return int
     */
    public function delete_old_messages(int $dt)
    {
        $messages_1 = $this->fluent->from('messages')
                                   ->select(null)
                                   ->select('receiver')
                                   ->where('location = ?', $this->site_config['pm']['deleted'])
                                   ->where('added <= ?', $dt);

        $this->fluent->delete('messages')
                     ->where('location = ?', $this->site_config['pm']['deleted'])
                     ->where('added <= ?', $dt)
                     ->execute();

        $messages_2 = $this->fluent->from('messages')
                                   ->select(null)
                                   ->select('receiver')
                                   ->where('location = ?', $this->site_config['pm']['inbox'])
                                   ->where('added <= ?', $dt);

        $this->fluent->delete('messages')
                     ->where('location = ?', $this->site_config['pm']['inbox'])
                     ->where('added <= ?', $dt)
                     ->execute();

        $i = 0;
        foreach ($messages_1 as $message) {
            ++$i;
            $this->cache->delete('inbox_' . $message['receiver']);
        }
        foreach ($messages_2 as $message) {
            ++$i;
            $this->cache->delete('inbox_' . $message['receiver']);
        }

        return $i;
    }

    /**
     * @param int    $userid
     * @param int    $location
     * @param int    $limit
     * @param int    $offset
     * @param string $orderby
     *
     * @throws Exception
     *
     * @return array|bool|Select
     */
    public function get_messages(int $userid, int $location, int $limit, int $offset, string $orderby)
    {
        $messages = $this->fluent->from('messages AS m');
        if ($location === $this->site_config['pm']['sent']) {
            $messages = $messages->where('sender = ?', $userid)
                                 ->where('location = ?', $this->site_config['pm']['inbox']);
        } else {
            $messages = $messages->where('receiver = ?', $userid)
                                 ->where('location = ?', $location);
        }
        $messages = $messages->select(null)
                             ->select('m.poster')
                             ->select('m.sender')
                             ->select('m.receiver')
                             ->select('m.added')
                             ->select('m.subject')
                             ->select('m.unread')
                             ->select('m.urgent')
                             ->select('m.id AS message_id')
                             ->select('f.id AS friend')
                             ->select('b.id AS blocked')
                             ->select('u.id');
        if ($location === $this->site_config['pm']['sent']) {
            $messages = $messages->leftJoin('users AS u ON m.receiver = u.id');
        } else {
            $messages = $messages->leftJoin('users AS u ON m.sender = u.id');
        }
        $messages = $messages->leftJoin('friends AS f ON m.receiver = f.userid AND m.sender = f.friendid')
                             ->leftJoin('blocks AS b ON m.receiver = b.userid AND m.sender = b.blockid')
                             ->limit($limit)
                             ->offset($offset)
                             ->orderBy($orderby)
                             ->fetchAll();

        return $messages;
    }
}
