<?php

declare(strict_types = 1);

use Delight\Auth\AuthError;
use Delight\Auth\NotLoggedInException;
use DI\DependencyException;
use DI\NotFoundException;
use Envms\FluentPDO\Literal;
use MatthiasMullie\Scrapbook\Exception\UnbegunTransaction;
use Pu239\Database;
use Pu239\Message;
use Pu239\Session;
use Spatie\Image\Exceptions\InvalidManipulation;

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_pager.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_bbcode.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $container, $site_config, $CURUSER;

$HTMLOUT = '';

/**
 * @param $val
 *
 * @return string
 */
function calctime($val)
{
    $days = intval($val / 86400);
    $val -= $days * 86400;
    $hours = intval($val / 3600);
    $val -= $hours * 3600;
    $mins = intval($val / 60);
    //$secs = $val - ($mins * 60);

    return "$days " . _('days') . ", $hours " . _('hrs') . ", $mins " . _('minutes') . '';
}

/**
 *
 * @param array $tids
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws AuthError
 * @throws NotLoggedInException
 * @throws \Envms\FluentPDO\Exception
 * @throws UnbegunTransaction
 * @throws \PHPMailer\PHPMailer\Exception
 * @throws InvalidManipulation
 *
 * @return bool|int
 */
function notify_owner(array $tids)
{
    global $container, $site_config;

    if (empty($tids)) {
        return false;
    }
    $fluent = $container->get(Database::class);
    $torrents = $fluent->from('torrents')
                       ->select(null)
                       ->select('id')
                       ->select('owner')
                       ->select('name')
                       ->where('id', $tids)
                       ->fetch();

    $dt = TIME_NOW;
    $subject = _('Dead Torrent Notice');
    $values = [];
    foreach ($torrents as $torrent) {
        $msg = _fe('Torrent {0}{1}{2} will soon be deleted. Please re-seed this torrent to avoid deletion.', "[url={$site_config['paths']['baseurl']}/details.php?id={$torrent['id']}]", format_comment($torrent['name']), '[/url]');
        $values[] = [
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
               ->where('tid = ?', $torrent['id'])
               ->execute();
    }
    if (!empty($values)) {
        $messages_class = $container->get(Message::class);
        $messages_class->insert($values);

        return count($values);
    }

    return false;
}

if (!empty($_POST['remove'])) {
    $deleted = notify_owner($_POST['remove']);
    $session = $container->get(Session::class);
    if ($deleted) {
        $session->set('is-success', _('Torrent Owner Notified'));
    } else {
        $session->set('is-success', _('Torrent Notification Failed'));
    }
}
// Give 'em 7 days to seed back their torrent (no peers, not seeded with in x days)
$x_time = 604800; // Delete Routine 1 // 7 days
// Give 'em 7 days to seed back their torrent (no peers, not snatched in x days)
$y_time = 2419200; // Delete Routine 2 // 28 days
// Give 'em 2 days to seed back their torrent (no seeder activity within x hours of torrent upload)
$z_time = 2 * 86400; // Delete Routine 3 // 2 days
$dx_time = get_date(TIME_NOW - $x_time, 'MYSQL', 1, 0);
$dy_time = TIME_NOW - $y_time;
$dz_time = TIME_NOW - $z_time;

$dead = $ids = [];
$fluent = $container->get(Database::class);
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
        'reason' => new Literal('VALUES(reason)'),
    ];
    $fluent->insertInto('deathrow')
           ->values($values)
           ->onDuplicateKeyUpdate($update)
           ->execute();
}

$count = $fluent->from('deathrow')
                ->select(null)
                ->select('COUNT(uid) AS count')
                ->fetch('count');

if ($count) {
    $perpage = 25;
    $pager = pager($perpage, $count, 'staffpanel.php?tool=deathrow&amp;');
    $torrents = $fluent->from('deathrow')
                       ->orderBy('username')
                       ->limit($pager['pdo']['limit'])
                       ->offset($pager['pdo']['offset'])
                       ->fetchAll();

    $HTMLOUT .= "
        <h1 class='has-text-centered'>" . _pfe('{0} Torrent On Deathrow', '{0} Torrents On Deathrow', $count) . '</h1>' . ($count > $perpage ? $pager['pagertop'] : '') . "
        <form action='' method='post' enctype='multipart/form-data' accept-charset='utf-8'>";
    $heading = '
        <tr>
            <th>' . _('Username') . '</th>
            <th>' . _('Torrent name') . '</th>
            <th>' . _('Delete Reason') . '</th>
            <th>' . _(' Notified') . "</th>
            <th class='has-text-centered w-1'><input type='checkbox' id='checkThemAll' class='tooltipper' title='Select All'></th>
        </tr>";
    $body = '';
    foreach ($torrents as $queued) {
        if ($queued['reason'] == 1) {
            $reason = _fe('no peers, not seeded within {0}', calctime($x_time));
        } elseif ($queued['reason'] == 2) {
            $reason = _fe('no peers, not snatched in {0}', calctime($y_time));
        } else {
            $reason = _fe('no seeder activity within {0} on new torrent', calctime($z_time));
        }
        $id = (int) $queued['tid'];
        $body .= '
        <tr>' . ($CURUSER['class'] >= UC_STAFF ? '
            <td>' . format_username((int) $queued['uid']) . '</td>' : '
            <td>' . _('Hidden') . '</td>') . "
            <td><a href='{$site_config['paths']['baseurl']}/details.php?id={$id}&amp;hit=1'>" . format_comment($queued['torrent_name']) . "</a></td>
            <td>{$reason}</td>
            <td>" . get_date((int) $queued['notified'], 'LONG', 0, 1) . "</td>
            <td><input type='checkbox' name='remove[]' value='{$id}' class='tooltipper' title='" . _('Delete') . "'></td>
        </tr>";
    }
    $HTMLOUT .= main_table($body, $heading) . ($count > $perpage ? $pager['pagerbottom'] : '');
    $HTMLOUT .= "
        <div class='has-text-centered margin20'>
            <input type='submit' name='submit' class='button is-small' value='" . _(' Notify') . "'>
        </div>
        </form>";
    $title = _('Deatchrow');
    $breadcrumbs = [
        "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
        "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
    ];
    echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
} else {
    $HTMLOUT = "<h1 class='has-text-centered'>" . _(' Torrents On Deathrow') . '</h1>';
    $HTMLOUT .= stdmsg(_('Awesome'), _('There are not torrents on deathrow'));
    $title = _('Deathrow');
    $breadcrumbs = [
        "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
        "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
    ];
    echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
}
