<?php

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'function_users.php';
check_user_status();

$check = isset($_POST['type']) ? $_POST['type'] : '';
$fields = [
    'comment' => 'comments',
    'topic' => 'topics',
    'post' => 'posts',
    'usercomment' => 'usercomments',
    'request' => 'requests',
    'offer' => 'offers',
    'torrent' => 'torrents',
];

comment_like_unlike($fields);

/**
 * @param $fields
 *
 * @throws \Envms\FluentPDO\Exception
 */
function comment_like_unlike($fields)
{
    $lang = array_merge(load_language('global'), load_language('ajax_like'));
    $id = $csrf = $type = $current = '';
    extract($_POST);
    global $CURUSER, $session, $cache, $fluent;

    $id = (int) $id;
    header('content-type: application/json');
    if (empty($csrf) || !$session->validateToken($csrf)) {
        echo json_encode(['label' => 'Invalid CSRF Token']);
        die();
    }
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

    $sql = 'SELECT COUNT(id) AS count FROM likes WHERE user_id = ' . sqlesc($CURUSER['id']) . " AND {$type}_id = " . sqlesc($id);
    $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
    $data = mysqli_fetch_assoc($res);
    $table = 'comments';
    if ($type === 'topic' || $type === 'post' || $type === 'usercomment') {
        $table = $fields[$type];
    }
    if ($data['count'] == 0 && $current === 'Like') {
        $sql = "INSERT INTO likes ({$type}_id, user_id) VALUES (" . sqlesc($id) . ', ' . sqlesc($CURUSER['id']) . ')';
        $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
        $sql = "UPDATE $table SET user_likes = user_likes + 1 WHERE id = " . sqlesc($id);
        $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
        $cache->delete("{$type}_user_likes_" . $id);
        $cache->delete('latest_comments_');
        $data['label'] = 'Unlike';
        $data['list'] = 'you like this';
    } elseif ($data['count'] == 1 && $current === 'Unlike') {
        $sql = "DELETE FROM likes WHERE {$type}_id = " . sqlesc($id) . ' AND user_id = ' . sqlesc($CURUSER['id']);
        $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
        $sql = "UPDATE $table SET user_likes = user_likes - 1 WHERE id = " . sqlesc($id);
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
        ->where('user_id != ?', $CURUSER['id']);
    foreach ($sql as $row) {
        $rows[] = format_username($row['user_id']);
    }
    if (!empty($rows)) {
        $data['list'] = implode(', ', $rows) . (!empty($data['list']) ? ' and ' . $data['list'] : ' like' . plural(count($rows)) . ' this');
    }
    $data['class'] = "tot-$id";

    echo json_encode($data);
    die();
}
