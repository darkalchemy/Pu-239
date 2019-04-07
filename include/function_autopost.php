<?php

/**
 * @param string $subject
 * @param string $body
 *
 * @throws \Envms\FluentPDO\Exception
 */
function auto_post($subject = 'Error - Subject Missing', $body = 'Error - No Body')
{
    global $CURUSER, $site_config, $cache, $fluent, $message_stuffs;

    if (user_exists($site_config['chatbot']['id'])) {
        $topicid = $fluent->from('topics')
                          ->select(null)
                          ->select('id')
                          ->where('forum_id=?', $site_config['staff_forums'][0])
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
                'topic_count' => new Envms\FluentPDO\Literal('topic_count + 1'),
            ];
            $fluent->update('forums')
                   ->set($set)
                   ->where('id=?', $site_config['staff_forums'][0])
                   ->execute();
        }

        $values = [
            'topic_id' => $topicid,
            'user_id' => $site_config['chatbot']['id'],
            'added' => TIME_NOW,
            'body' => $body,
            'ip' => inet_pton(getip()),
        ];
        $postid = $fluent->insertInto('posts')
                         ->values($values)
                         ->execute();

        $set = [
            'last_post' => $postid,
        ];
        $fluent->update('topics')
               ->set($set)
               ->where('id=?', $topicid)
               ->execute();

        $set = [
            'post_count' => new Envms\FluentPDO\Literal('post_count + 1'),
        ];
        $fluent->update('forums')
               ->set($set)
               ->where('id=?', $site_config['staff_forums'][0])
               ->execute();

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
        $message_stuffs->insert($values);
    }
}
