<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Cache;
use Pu239\Database;

require_once __DIR__ . '/../../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
$user = check_user_status('like');

$fields = [
    'comment' => 'comments',
    'topic' => 'topics',
    'post' => 'posts',
    'usercomment' => 'usercomments',
    'request' => 'requests',
    'offer' => 'offers',
    'torrent' => 'torrents',
];

if (!empty($user) && is_array($user)) {
    comment_like_unlike($fields, $user);
}

/**
 * @param array $fields
 * @param array $user
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 */
function comment_like_unlike(array $fields, array $user)
{
    global $container;

    $id = (int) $_POST['id'];
    $type = $_POST['type'];
    $current = $_POST['current'];
    header('content-type: application/json');
    if (!array_key_exists($type, $fields)) {
        echo json_encode(['label' => 'Invalid Data Type']);
        die();
    }
    if (!is_int($id)) {
        echo json_encode(['label' => 'Invalid ID ' . $id]);
        die();
    }

    if ($type === 'torrent') {
        $type = 'comment';
    }

    $sql = 'SELECT COUNT(id) AS count FROM likes WHERE user_id=' . sqlesc($user['id']) . " AND {$type}_id=" . sqlesc($id);
    $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
    $data = mysqli_fetch_assoc($res);
    $table = 'comments';
    if ($type === 'topic' || $type === 'post' || $type === 'usercomment') {
        $table = $fields[$type];
    }
    $cache = $container->get(Cache::class);
    $fluent = $container->get(Database::class);
    if ($data['count'] == 0 && $current === 'Like') {
        $sql = "INSERT INTO likes ({$type}_id, user_id) VALUES (" . sqlesc($id) . ', ' . sqlesc($user['id']) . ')';
        $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
        $sql = "UPDATE $table SET user_likes = user_likes + 1 WHERE id=" . sqlesc($id);
        $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
        $cache->delete("{$type}_user_likes_" . $id);
        $cache->delete('latest_comments_');
        $data['label'] = 'Unlike';
        $data['list'] = 'you like this';
    } elseif ($data['count'] == 1 && $current === 'Unlike') {
        $sql = "DELETE FROM likes WHERE {$type}_id=" . sqlesc($id) . ' AND user_id=' . sqlesc($user['id']);
        $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
        $sql = "UPDATE $table SET user_likes = user_likes - 1 WHERE id=" . sqlesc($id);
        $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
        $cache->delete("{$type}_user_likes_" . $id);
        $cache->delete('latest_comments_');
        $data['label'] = 'Like';
        $data['list'] = '';
    } elseif ($data['count'] == 1 && $current === 'Like') {
        $data['label'] = 'Unlike';
        $data['list'] = 'you like this';
    } else {
        $data['label'] = 'you lost me';
    }
    $sql = $fluent->from('likes')
                  ->select(null)
                  ->select('user_id')
                  ->where("{$type}_id = ?", $id)
                  ->where('user_id != ?', $user['id']);
    foreach ($sql as $row) {
        $rows[] = format_username((int) $row['user_id']);
    }
    if (!empty($rows)) {
        $data['list'] = implode(', ', $rows) . (!empty($data['list']) ? ' and ' . $data['list'] : ' like' . plural(count($rows)) . ' this');
    }
    $data['class'] = "tot-$id";

    echo json_encode($data);
    die();
}
