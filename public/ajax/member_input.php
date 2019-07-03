<?php

declare(strict_types = 1);

use Pu239\Database;
use Pu239\Peer;
use Pu239\Session;
use Pu239\Snatched;
use Pu239\User;

require_once __DIR__ . '/../../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
$curuser = check_user_status();
global $container;

$posted_action = isset($_POST['action']) ? htmlsafechars($_POST['action']) : (isset($_GET['action']) ? htmlsafechars($_GET['action']) : '');
$valid_actions = [
    'flush_torrents',
    'staff_notes',
    'watched_user',
];

$referer = $_SERVER['HTTP_REFERER'] . '#general';
$action = in_array($posted_action, $valid_actions) ? $posted_action : '';
$session = $container->get(Session::class);
if (!isset($action)) {
    $session->set('is-danger', 'Access Not Allowed');
    header('Location: ' . $referer);
    die();
}
$id = $curuser['class'] < UC_STAFF ? $curuser['id'] : (int) $_POST['id'];
if ($id === 0) {
    $session->set('is-danger', 'Invalid User ID');
    header('Location: ' . $referer);
    die();
}
$users_class = $container->get(User::class);
$user = $users_class->getUserFromId($id);
switch ($action) {
    case 'flush_torrents':
        $snatched_class = $container->get(Snatched::class);
        $peers_class = $container->get(Peer::class);
        $snatched_class->flush($id);
        $count = $peers_class->flush($id);
        if ($id === $curuser['id']) {
            $values = [
                'added' => TIME_NOW,
                'txt' => "[url={$site_config['paths']['baseurl']}/userdetails.php?id={$curuser['id']}]{$curuser['username']}[/url] flushed {$count} torrents.",
            ];
        } elseif ($id !== $curuser['id'] && $curuser['class'] >= UC_STAFF) {
            $values = [
                'added' => TIME_NOW,
                'txt' => "Staff Flush: [url={$site_config['paths']['baseurl']}/userdetails.php?id={$curuser['id']}]{$curuser['username']}[/url] flushed {$count} torrents for [url={$site_config['paths']['baseurl']}/userdetails.php?id={$id}]{$user['username']}[/url]",
            ];
        }
        $fluent = $container->get(Database::class);
        $fluent->insertInto('sitelog')
               ->values($values)
               ->execute();
        break;

    case 'staff_notes':
        if ($curuser['class'] < UC_STAFF) {
            stderr('Error', 'How did you get here?');
        }
        $posted_notes = isset($_POST['new_staff_note']) ? htmlsafechars($_POST['new_staff_note']) : '';
        if ($id !== $curuser['id'] && $curuser['class'] > $user['class']) {
            $update = [
                'staff_notes' => $posted_notes,
            ];
            $users_class->update($update, $id);
            write_log("{$curuser['username']} edited member [url={$site_config['paths']['baseurl']}/userdetails.php?id={$id}]{$user['username']}[/url] staff notes. Changes made:<br>Was:<br>" . htmlsafechars((string) $user['staff_notes']) . '<br>is now:<br>' . $posted_notes);
        }
        header('Location: ' . $referer);
        break;

    case 'watched_user':
        if ($curuser['class'] < UC_STAFF) {
            stderr('Error', 'How did you get here?');
        }

        $posted = isset($_POST['watched_reason']) ? htmlsafechars($_POST['watched_reason']) : '';
        if ($id !== $curuser['id'] || $curuser['class'] < $user['class']) {
            if (isset($_POST['add_to_watched_users']) && $_POST['add_to_watched_users'] === 'yes' && $user['watched_user'] == 0) {
                $update['watched_user'] = TIME_NOW;
                write_log("{$curuser['username']} added member [url={$site_config['paths']['baseurl']}/userdetails.php?id={$id}]{$user['username']}[/url] to watched users.");
            } elseif (isset($_POST['add_to_watched_users']) && $_POST['add_to_watched_users'] === 'no' && $user['watched_user'] > 0) {
                $update['watched_user'] = 0;
                write_log("{$curuser['username']} removed member [url={$site_config['paths']['baseurl']}/userdetails.php?id={$id}]{$user['username']}[/url] from watched users. <br>{$user['username']} had been on the list since " . get_date((int) $user['watched_user'], 'LONG'));
            }
            if ($_POST['watched_reason'] !== $user['watched_user_reason']) {
                $update['watched_user_reason'] = $posted;
                write_log("{$curuser['username']} changed watched user text for: [url={$site_config['paths']['baseurl']}/userdetails.php?id={$id}]{$user['username']}[/url] Changes made:<br>Text was:<br>" . htmlsafechars((string) $user['watched_user_reason']) . '<br>Is now:<br>' . $posted);
            }
            if (!empty($update)) {
                $users_class->update($update, $id);
            }
        }
        header('Location: ' . $referer);
        break;
}
