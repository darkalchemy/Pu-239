<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Database;

require_once INCL_DIR . 'function_returnto.php';
$user = check_user_status();
if ($_GET['action'] === 'mark_all_as_read') {
    mark_as_read($user);
    redirect();
} elseif ($_GET['action'] === 'mark_all_as_unread') {
    mark_as_unread($user);
    redirect();
}

function redirect()
{
    $url = !empty($_SERVER['HTTP_REFERER']) ? get_return_to($_SERVER['HTTP_REFERER']) : get_return_to($_SERVER['QUERY_STRING']);
    if (!empty($url)) {
        header('Location: ' . $url);
    } else {
        header('Location: ' . $_SERVER['PHP_SELF'] . '?m=1');
    }
    die();
}

/**
 * @param array $user
 *
 * @throws \DI\DependencyException
 * @throws \DI\NotFoundException
 * @throws \Envms\FluentPDO\Exception
 */
function mark_as_read(array $user)
{
    global $container;

    $fluent = $container->get(Database::class);
    $cache = $container->get(Cache::class);
    $query = get_topics();
    foreach ($query as $topic) {
        $values = [
            'user_id' => $user['id'],
            'topic_id' => $topic['id'],
            'last_post_read' => $topic['last_post'],
        ];
        $update = [
            'last_post_read' => $topic['last_post'],
        ];
        $fluent->insertInto('read_posts', $values)
               ->onDuplicateKeyUpdate($update)
               ->execute();
        $cache->delete('last_read_post_' . $topic['id'] . '_' . $user['id']);
        $cache->delete('sv_last_read_post_' . $topic['id'] . '_' . $user['id']);
    }
}

/**
 * @param array $user
 *
 * @throws \DI\DependencyException
 * @throws \DI\NotFoundException
 * @throws \Envms\FluentPDO\Exception
 */
function mark_as_unread(array $user)
{
    global $container;

    $fluent = $container->get(Database::class);
    $cache = $container->get(Cache::class);
    $query = get_topics();
    foreach ($query as $topic) {
        $values = [
            'user_id' => $user['id'],
            'topic_id' => $topic['id'],
            'last_post_read' => $topic['first_post'],
        ];
        $update = [
            'last_post_read' => $topic['first_post'],
        ];
        $fluent->insertInto('read_posts', $values)
               ->onDuplicateKeyUpdate($update)
               ->execute();

        $cache->delete('last_read_post_' . $topic['id'] . '_' . $user['id']);
        $cache->delete('sv_last_read_post_' . $topic['id'] . '_' . $user['id']);
    }
}

/**
 * @throws \DI\DependencyException
 * @throws \DI\NotFoundException
 * @throws \Envms\FluentPDO\Exception
 *
 * @return mixed
 */
function get_topics()
{
    global $container, $site_config;

    $dt = TIME_NOW - ($site_config['forum_config']['readpost_expiry'] * 86400);
    $fluent = $container->get(Database::class);
    $query = $fluent->from('topics AS t')
                    ->select(null)
                    ->select('t.id')
                    ->select('t.last_post')
                    ->select('t.first_post - 1 AS first_post')
                    ->leftJoin('posts AS p ON t.last_post = p.id')
                    ->where('p.added > ?', $dt)
                    ->fetchAll();

    return $query;
}
