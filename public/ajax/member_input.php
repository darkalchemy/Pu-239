<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Session;

require_once __DIR__ . '/../../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
check_user_status();
$posted_action = (isset($_POST['action']) ? htmlsafechars($_POST['action']) : (isset($_GET['action']) ? htmlsafechars($_GET['action']) : ''));
$valid_actions = [
    'flush_torrents',
    'staff_notes',
    'watched_user',
];

$action = in_array($posted_action, $valid_actions) ? $posted_action : '';
global $container, $CURUSER;

$session = $container->get(Session::class);
if (empty($_POST)) {
    $session->set('is-danger', 'Access Not Allowed');
    header("Location: {$site_config['paths']['baseurl']}");
    die();
}
if ($action == '') {
    $session->set('is-danger', 'Access Not Allowed');
    header('Location: ' . $site_config['paths']['baseurl']);
} else {
    $cache = $container->get(Cache::class);
    switch ($action) {
        case 'flush_torrents':
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            //== if it's the member flushing
            if ($id == $CURUSER['id']) {
                //=== catch any missed snatched stuff thingies to stop ghost leechers from getting peers (if the peers they have drop off)
                sql_query('UPDATE snatched SET seeder = "no" WHERE userid=' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
                //=== flush dem torrents!!! \o/
                sql_query('DELETE FROM peers WHERE userid=' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
                $number_of_torrents_flushed = mysqli_affected_rows($mysqli);
                //=== add it to the log
                // come back
                sql_query('INSERT INTO `sitelog` (`id`, `added`, `txt`) VALUES (NULL , ' . TIME_NOW . ', ' . sqlesc("[url={$site_config['paths']['baseurl']}/userdetails.php?id={$CURUSER['id']}]{$CURUSER['username']}[/url] flushed {$number_of_torrents_flushed} torrents.") . ')') or sqlerr(__FILE__, __LINE__);
            } //=== if it's staff flushing for a member
            elseif ($id !== $CURUSER['id'] && $CURUSER['class'] >= UC_STAFF) {
                //=== it's a staff...
                $res_get_info = sql_query('SELECT username FROM users WHERE id=' . sqlesc($id));
                $user_get_info = mysqli_fetch_assoc($res_get_info);
                //=== catch any missed snatched stuff thingies to stop ghost leechers from getting peers (if the peers they have drop off)
                sql_query('UPDATE snatched SET seeder = "no" WHERE userid=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
                //=== flush dem torrents!!! \o/
                sql_query('DELETE FROM peers WHERE userid=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
                $number_of_torrents_flushed = mysqli_affected_rows($mysqli);
                //=== add it to the log
                sql_query('INSERT INTO `sitelog` (`id`, `added`, `txt`) VALUES (NULL , ' . TIME_NOW . ', ' . sqlesc("Staff Flush: [url={$site_config['paths']['baseurl']}/userdetails.php?id={$CURUSER['id']}]{$CURUSER['username']}[/url] flushed {$number_of_torrents_flushed} torrents for [url={$site_config['paths']['baseurl']}/userdetails.php?id={$id}]{$user_get_info['username']}[/url]") . ')') or sqlerr(__FILE__, __LINE__);
            }
            break;

        case 'staff_notes':
            if ($CURUSER['class'] < UC_STAFF) {
                stderr('Error', 'How did you get here?');
            }
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            $posted_notes = isset($_POST['new_staff_note']) ? htmlsafechars($_POST['new_staff_note']) : '';
            //=== make sure they are staff, not editing their own and playing nice :P
            $staff_notes_res = sql_query('SELECT staff_notes, class, username FROM users WHERE id=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
            $staff_notes_arr = mysqli_fetch_assoc($staff_notes_res);
            if ($id !== $CURUSER['id'] && $CURUSER['class'] > $staff_notes_arr['class']) {
                //=== add / edit staff_notes
                sql_query('UPDATE users SET staff_notes = ' . sqlesc($posted_notes) . ' WHERE id =' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
                $cache->update_row('user_' . $id, [
                    'staff_notes' => $posted_notes,
                ], $site_config['expires']['user_cache']);
                //=== add it to the log
                write_log("{$CURUSER['username']} edited member [url={$site_config['paths']['baseurl']}/userdetails.php?id={$id}]{$staff_notes_arr['username']}[/url] staff notes. Changes made:<br>Was:<br>" . htmlsafechars((string) $staff_notes_arr['staff_notes']) . '<br>is now:<br>' . htmlsafechars((string) $_POST['new_staff_note']));
            }
            header('Location: ' . $_SERVER['PHP_SELF'] . '?id=' . $id . '&sn=1');
            break;

        case 'watched_user':
            if ($CURUSER['class'] < UC_STAFF) {
                stderr('Error', 'How did you get here?');
            }
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            $posted = isset($_POST['watched_reason']) ? htmlsafechars($_POST['watched_reason']) : '';
            //=== make sure they are staff, not editing their own and playing nice :P
            $watched_res = sql_query('SELECT watched_user, watched_user_reason, class, username FROM users WHERE id=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
            $watched_arr = mysqli_fetch_assoc($watched_res);
            if ($id !== $CURUSER['id'] || $CURUSER['class'] < $watched_arr['class']) {
                //=== add / remove from watched users
                if (isset($_POST['add_to_watched_users']) && $_POST['add_to_watched_users'] === 'yes' && $watched_arr['watched_user'] == 0) {
                    //=== set them to watched user
                    sql_query('UPDATE users SET watched_user = ' . TIME_NOW . ' WHERE id=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
                    $cache->update_row('user_' . $id, [
                        'watched_user' => TIME_NOW,
                    ], $site_config['expires']['user_cache']);
                    //=== add it to the log
                    write_log("{$CURUSER['username']} added member [url={$site_config['paths']['baseurl']}/userdetails.php?id={$id}]{$watched_arr['username']}[/url] to watched users.");
                }
                if (isset($_POST['add_to_watched_users']) && $_POST['add_to_watched_users'] === 'no' && $watched_arr['watched_user'] > 0) {
                    //=== remove them from watched users
                    sql_query('UPDATE users SET watched_user = 0 WHERE id=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
                    $cache->update_row('user_' . $id, [
                        'watched_user' => 0,
                    ], $site_config['expires']['user_cache']);
                    //=== add it to the log
                    write_log("{$CURUSER['username']} removed member [url={$site_config['paths']['baseurl']}/userdetails.php?id={$id}]{$watched_arr['username']}[/url] from watched users. <br>{$watched_arr['username']} had been on the list since " . get_date((int) $watched_arr['watched_user'], 'LONG'));
                }
                //=== only change if different
                if ($_POST['watched_reason'] !== $watched_arr['watched_user_reason']) {
                    //=== edit watched users text
                    sql_query('UPDATE users SET watched_user_reason = ' . sqlesc($posted) . ' WHERE id=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
                    $cache->update_row('user_' . $id, [
                        'watched_user_reason' => $posted,
                    ], $site_config['expires']['user_cache']);
                    //=== add it to the log
                    write_log("{$CURUSER['username']} changed watched user text for: [url={$site_config['paths']['baseurl']}/userdetails.php?id={$id}]{$watched_arr['username']}[/url] Changes made:<br>Text was:<br>" . htmlsafechars((string) $watched_arr['watched_user_reason']) . '<br>Is now:<br>' . htmlsafechars((string) $_POST['watched_reason']));
                }
            }
            header('Location: ' . $_SERVER['PHP_SELF'] . '?id=' . $id . '&wu=1');
            break;
    }
}
