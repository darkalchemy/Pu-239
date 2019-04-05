<?php

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_pager.php';
require_once INCL_DIR . 'function_html.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $CURUSER, $lang, $fluent, $session;

$lang = array_merge($lang, load_language('ad_deathrow'));

$HTMLOUT = '';

/**
 * @param $val
 *
 * @return string
 */
function calctime($val)
{
    global $lang;

    $days = intval($val / 86400);
    $val -= $days * 86400;
    $hours = intval($val / 3600);
    $val -= $hours * 3600;
    $mins = intval($val / 60);
    //$secs = $val - ($mins * 60);

    return "$days {$lang['deathrow_days']}, $hours {$lang['deathrow_hrs']}, $mins {$lang['deathrow_minutes']}";
}

/**
 * @param array $tids
 *
 * @return bool|int
 *
 * @throws \Envms\FluentPDO\Exception
 */
function notify_owner(array $tids)
{
    global $fluent, $site_config, $lang, $message_stuffs;

    if (empty($tids)) {
        return false;
    }
    $torrents = $fluent->from('torrents')
        ->select(null)
        ->select('id')
        ->select('owner')
        ->select('name')
        ->where('id', $tids)
        ->fetchAll();

    $dt = TIME_NOW;
    $subject = $lang['deathrow_dead'];
    $values = [];
    foreach ($torrents as $torrent) {
        $msg = "{$lang['deathrow_torrent']} [url={$site_config['paths']['baseurl']}/details.php?id={$torrent['id']}]\"{$torrent['name']}\"[/url] {$lang['deathrow_warn']}";
        $values[] = [
            'sender' => 0,
            'receiver' => $torrent['owner'],
            'added' => $dt,
            'msg' => $msg,
            'subject' => $subject,
        ];
        $set = [
            'notified' => $dt,
        ];
        $fluent->update('deathrow')
            ->set($set)
            ->where('tid=?', $torrent['id'])
            ->execute();
    }
    if (!empty($values)) {
        $message_stuffs->insert($values);

        return count($values);
    }

    return false;
}

if (!empty($_POST['remove'])) {
    $deleted = notify_owner($_POST['remove']);
    if ($deleted) {
        $session->set('is-success', $lang['deathrow_owner'] . plural($deleted) . $lang['deathrow_notified']);
    } else {
        $session->set('is-success', $lang['deathrow_failed']);
    }
}
// Give 'em 7 days to seed back their torrent (no peers, not seeded with in x days)
$x_time = 604800; // Delete Routine 1 // 7 days
// Give 'em 7 days to seed back their torrent (no peers, not snatched in x days)
$y_time = 2419200; // Delete Routine 2 // 28 days
// Give 'em 2 days to seed back their torrent (no seeder activity within x hours of torrent upload)
$z_time = 2 * 86400; // Delete Routine 3 // 2 days
$dx_time = TIME_NOW - $x_time;
$dy_time = TIME_NOW - $y_time;
$dz_time = TIME_NOW - $z_time;

$dead = $ids = [];
$query1 = $fluent->from('torrents AS t')
    ->select(null)
    ->select('t.id')
    ->select('t.name')
    ->select('t.owner')
    ->select('u.username')
    ->leftJoin('users AS u ON t.owner = u.id')
    ->where('t.seeders + t.leechers = 0')
    ->where('t.last_action < ?', $dx_time);

foreach ($query1 as $arr) {
    $dead[] = [
        'tid' => $arr['id'],
        'torrent_name' => $arr['name'],
        'uid' => $arr['owner'],
        'username' => $arr['username'],
        'reason' => 1,
    ];
    $ids[] = $arr['id'];
}

$query2 = $fluent->from('torrents AS t')
    ->select(null)
    ->select('t.id')
    ->select('t.name')
    ->select('t.owner')
    ->select('s.complete_date')
    ->select('u.username')
    ->leftJoin('users AS u ON t.owner = u.id')
    ->leftJoin('snatched AS s ON t.id=s.torrentid')
    ->where('t.seeders + t.leechers = 0')
    ->where('t.last_action < ?', $dx_time)
    ->where('s.complete_date>0');

foreach ($query2 as $arr) {
    if ($arr['complete_date'] < $dy_time && !in_array($arr['id'], $ids)) {
        $dead[] = [
            'tid' => $arr['id'],
            'torrent_name' => $arr['name'],
            'uid' => $arr['owner'],
            'username' => $arr['username'],
            'reason' => 2,
        ];
    }
}

$query3 = $fluent->from('torrents AS t')
    ->select(null)
    ->select('t.id')
    ->select('t.name')
    ->select('t.added')
    ->select('t.owner')
    ->select('p.last_action')
    ->select('u.username')
    ->leftJoin('users AS u ON t.owner = u.id')
    ->leftJoin('peers AS p ON t.id=p.torrent')
    ->where('t.seeders + t.leechers = 0')
    ->where('t.last_action < ?', $dx_time)
    ->where('t.added < ?', TIME_NOW - 86400);

foreach ($query3 as $arr) {
    if (empty($arr['last_action']) && !in_array($arr['id'], $ids)) {
        $peer = $fluent->from('peers')
            ->select(null)
            ->select('id')
            ->where('torrent = ?', $arr['id'])
            ->where('seeder = "yes"')
            ->fetch('id');

        if (empty($peer)) {
            $dead[] = [
                'tid' => $arr['id'],
                'torrent_name' => $arr['name'],
                'uid' => $arr['owner'],
                'username' => $arr['username'],
                'reason' => 3,
            ];
        }
    }
}

$fluent->delete('deathrow')
    ->from('deathrow')
    ->innerJoin('peers AS p ON deathrow.tid=p.torrent')
    ->where('p.seeder = "yes"')
    ->execute();

foreach ($dead as $values) {
    $update = [
        'reason' => new Envms\FluentPDO\Literal('VALUES(reason)'),
    ];
    $fluent->insertInto('deathrow')
        ->values($values)
        ->onDuplicateKeyUpdate($update)
        ->execute();
}

$count = $fluent->from('deathrow')
    ->select(null)
    ->select('COUNT(*) AS count')
    ->fetch('count');

if ($count) {
    $perpage = 25;
    $pager = pager($perpage, $count, 'staffpanel.php?tool=deathrow&amp;');
    $torrents = $fluent->from('deathrow')
        ->orderBy('username')
        ->limit($pager['pdo'])
        ->fetchAll();

    $HTMLOUT .= "
        <h1 class='has-text-centered'>$count {$lang['deathrow_title']}</h1>" . ($count > $perpage ? $pager['pagertop'] : '') . "
        <form action='' method='post' accept-charset='utf-8'>";
    $heading = "
        <tr>
            <th>{$lang['deathrow_uname']}</th>
            <th>{$lang['deathrow_tname']}</th>
            <th>{$lang['deathrow_del_resn']}</th>
            <th>{$lang['deathrow_notified']}</th>
            <th class='has-text-centered w-1'><input type='checkbox' id='checkThemAll' class='tooltipper' title='Select All'></th>
        </tr>";
    $body = '';
    foreach ($torrents as $queued) {
        if ($queued['reason'] == 1) {
            $reason = $lang['deathrow_nopeer'] . calctime($x_time);
        } elseif ($queued['reason'] == 2) {
            $reason = $lang['deathrow_no_peers'] . calctime($y_time);
        } else {
            $reason = $lang['deathrow_no_seed'] . calctime($z_time) . $lang['deathrow_new_torr'];
        }
        $id = (int) $queued['tid'];
        $body .= '
        <tr>' . ($CURUSER['class'] >= UC_STAFF ? '
            <td>' . format_username($queued['uid']) . '</td>' : "
            <td>{$lang['deathrow_hidden']}</td>") . "
            <td><a href='{$site_config['paths']['baseurl']}/details.php?id={$id}&amp;hit=1'>" . htmlsafechars($queued['torrent_name']) . "</a></td>
            <td>{$reason}</td>
            <td>" . get_date($queued['notified'], 'LONG', 0, 1) . "</td>
            <td><input type='checkbox' name='remove[]' value='{$id}' class='tooltipper' title='{$lang['deathrow_delete']}'></td>
        </tr>";
    }
    $HTMLOUT .= main_table($body, $heading) . ($count > $perpage ? $pager['pagerbottom'] : '');
    $HTMLOUT .= "
        <div class='has-text-centered margin20'>
            <input type='submit' name='submit' class='button is-small' value='{$lang['deathrow_notify']}'>
        </div>
        </form>";
    echo stdhead($lang['deathrow_stdhead']) . wrapper($HTMLOUT) . stdfoot();
} else {
    $HTMLOUT = "<h1 class='has-text-centered'>{$lang['deathrow_title']}</h1>";
    $HTMLOUT .= stdmsg('Awesome', 'There are not torrents on deathrow');
    echo stdhead($lang['deathrow_stdhead0']) . wrapper($HTMLOUT) . stdfoot();
}
