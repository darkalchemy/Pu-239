<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Database;

$user = check_user_status();
global $container, $site_config;

$fluent = $container->get(Database::class);
$cache = $container->get(Cache::class);
$topic_id = isset($_GET['topic_id']) ? (int) $_GET['topic_id'] : (isset($_POST['topic_id']) ? (int) $_POST['topic_id'] : 0);
$last_post = isset($_GET['last_post']) ? (int) $_GET['last_post'] : (isset($_POST['last_post']) ? (int) $_POST['last_post'] : 0);
$values = [
    'user_id' => $user['id'],
    'topic_id' => $topic_id,
    'last_post_read' => $last_post,
];
$update = [
    'last_post_read' => $last_post,
];
$fluent->insertInto('read_posts', $values)
       ->onDuplicateKeyUpdate($update)
       ->execute();
$cache->delete('last_read_post_' . $topic_id . '_' . $user['id']);
$cache->delete('sv_last_read_post_' . $topic_id . '_' . $user['id']);
header('Location: ' . $_SERVER['PHP_SELF'] . '?action=view_unread_posts');
die();
