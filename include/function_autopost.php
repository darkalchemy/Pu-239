<?php

/**
 * @param string $subject
 * @param string $body
 */
function auto_post($subject = 'Error - Subject Missing', $body = 'Error - No Body')
{
    global $CURUSER, $site_config, $cache, $fluent;

    if (user_exists($site_config['chatBotID'])) {
        $topicid = $fluent->from('topics')
                ->select(null)
                ->select('id')
                ->where('forum_id = ?', $site_config['staff']['forumid'])
                ->where('topic_name = ?', $subject)
                ->fetch('id');
        if (!$topicid) {
            $values = [
                'user_id' => $site_config['chatBotID'],
                'forum_id' => $site_config['staff']['forumid'],
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
                ->where('id = ?', $site_config['staff']['forumid'])
                ->execute();
        }

        $values = [
            'topic_id' => $topicid,
            'user_id' => $site_config['chatBotID'],
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
            'post_count' => new Envms\FluentPDO\Literal('post_count + 1'),
        ];
        $fluent->update('forums')
            ->set($set)
            ->where('id = ?', $site_config['staff']['forumid'])
            ->execute();

        $cache->delete('last_posts_' . $CURUSER['class']);
        $cache->delete('forum_posts_' . $CURUSER['id']);

        $values = [
            'sender' => 0,
            'receiver' => $site_config['site']['owner'],
            'added' => TIME_NOW,
            'subject' => $subject,
            'msg' => $body,
        ];
        $fluent->insertInto('messages')
            ->values($values)
            ->execute();

        $cache->delete('inbox_' . $site_config['site']['owner']);
    }
}
