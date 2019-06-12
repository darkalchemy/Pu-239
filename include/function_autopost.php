<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Envms\FluentPDO\Literal;
use Pu239\Cache;
use Pu239\Database;
use Pu239\Message;

/**
 * @param string $subject
 * @param string $body
 *
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 * @throws NotFoundException
 *
 * @return array|null
 */
function auto_post($subject = 'Error - Subject Missing', $body = 'Error - No Body')
{
    global $container, $site_config, $CURUSER;

    $fluent = $container->get(Database::class);
    if (user_exists($site_config['chatbot']['id'])) {
        $topicid = $fluent->from('topics')
                          ->select(null)
                          ->select('id')
                          ->where('forum_id = ?', $site_config['staff_forums'][0])
                          ->where('topic_name = ?', $subject)
                          ->fetch('id');
        if (!$topicid) {
            $values = [
                'user_id' => $site_config['chatbot']['id'],
                'forum_id' => $site_config['staff_forums'][0],
                'topic_name' => $subject,
            ];
            $topicid = $fluent->insertInto('topics')
                              ->values($values)
                              ->execute();

            $set = [
                'topic_count' => new Literal('topic_count + 1'),
            ];
            $fluent->update('forums')
                   ->set($set)
                   ->where('id = ?', $site_config['staff_forums'][0])
                   ->execute();
        }

        $values = [
            'topic_id' => $topicid,
            'user_id' => $site_config['chatbot']['id'],
            'added' => TIME_NOW,
            'body' => $body,
        ];
        $postid = $fluent->insertInto('posts')
                         ->values($values)
                         ->execute();

        $set = [
            'last_post' => $postid,
        ];
        $fluent->update('topics')
               ->set($set)
               ->where('id = ?', $topicid)
               ->execute();

        $set = [
            'post_count' => new Literal('post_count + 1'),
        ];
        $fluent->update('forums')
               ->set($set)
               ->where('id = ?', $site_config['staff_forums'][0])
               ->execute();

        $cache = $container->get(Cache::class);
        $cache->delete('last_posts_' . $CURUSER['class']);
        $cache->delete('forum_posts_' . $CURUSER['id']);

        unset($values);
        $values[] = [
            'sender' => 0,
            'receiver' => $site_config['site']['owner'],
            'added' => TIME_NOW,
            'subject' => $subject,
            'msg' => $body,
        ];
        $messages_class = $container->get(Message::class);
        $messages_class->insert($values);

        return [
            'topicid' => $topicid,
            'postid' => $postid,
        ];
    }

    return null;
}
