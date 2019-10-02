<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Database;

require_once __DIR__ . '/../../include/bittorrent.php';
$user = check_user_status();
global $container;

$private = $_POST['private'];
$remove = $_POST['remove'];
$tid = $_POST['tid'];
header('content-type: application/json');
if (empty($tid)) {
    echo json_encode(['fail' => 'invalid']);
    die();
}
if (empty($user)) {
    echo json_encode(['fail' => 'csrf']);
    die();
}
$fluent = $container->get(Database::class);
$cache = $container->get(Cache::class);
if ($private === 'true') {
    $bookmark = $fluent->from('bookmarks')
                       ->select(null)
                       ->select('private')
                       ->where('torrentid = ?', $tid)
                       ->where('userid = ?', $user['id'])
                       ->fetch('private');

    if ($bookmark === 'yes') {
        $private = 'no';
        $text = _('Mark Bookmark Private!');
    } else {
        $private = 'yes';
        $text = _('Mark Bookmark Public!');
    }
    $set = [
        'private' => $private,
    ];

    $fluent->update('bookmarks')
           ->set($set)
           ->where('torrentid = ?', $tid)
           ->where('userid = ?', $user['id'])
           ->execute();

    $cache->delete('bookmarks_' . $user['id']);
    echo json_encode([
        'bookmark' => $private,
        'content' => 'private',
        'text' => $text,
        'tid' => $tid,
        'remove' => 'false',
    ]);
    die();
}

$bookmark = $fluent->from('bookmarks')
                   ->select(null)
                   ->select('id')
                   ->where('torrentid = ?', $tid)
                   ->where('userid = ?', $user['id'])
                   ->fetch('id');

if (!empty($bookmark)) {
    $fluent->delete('bookmarks')
           ->where('id = ?', $bookmark)
           ->execute();
    $cache->delete('bookmarks_' . $user['id']);
    echo json_encode([
        'content' => 'deleted',
        'text' => _('Add Bookmark'),
        'tid' => $tid,
        'remove' => $remove,
    ]);
    die();
} else {
    $values = [
        'userid' => $user['id'],
        'torrentid' => $tid,
    ];
    $fluent->insertInto('bookmarks')
           ->values($values)
           ->execute();
    $cache->delete('bookmarks_' . $user['id']);
    echo json_encode([
        'content' => 'added',
        'text' => _('Delete Bookmark'),
        'tid' => $tid,
        'remove' => $remove,
    ]);
    die();
}
