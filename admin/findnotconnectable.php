<?php

declare(strict_types = 1);

use Pu239\Database;
use Pu239\Message;
use Pu239\Session;

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_bbcode.php';
require_once INCL_DIR . 'function_torrenttable.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $container, $site_config, $CURUSER;

$HTMLOUT = '';

if (isset($_GET['action1']) && htmlsafechars($_GET['action1']) === 'list') {
    $res2 = sql_query("SELECT userid, seeder, torrent, agent FROM peers WHERE connectable = 'no' ORDER BY userid DESC") or sqlerr(__FILE__, __LINE__);

    $HTMLOUT .= "
    <ul class='level-center bg-06'>
        <li class='is-link margin10'>
            <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=findnotconnectable&amp;action=findnotconnectable&amp;action1=sendpm'>" . _('Send All not connectable Users A PM') . "</a>
        </li>
        <li class='is-link margin10'>
            <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=findnotconnectable&amp;action=findnotconnectable'>" . _('View the Log (Check this before PMing users)') . "</a>
        </li>
    </ul>
    <h1 class='has-text-centered'>" . _('Peers that are Not Connectable') . '</h1>';
    $result = sql_query("SELECT DISTINCT userid FROM peers WHERE connectable = 'no'");
    $count = mysqli_num_rows($result);
    @((mysqli_free_result($result) || (is_object($result) && (get_class($result) === 'mysqli_result'))) ? true : false);
    if (mysqli_num_rows($res2) == 0) {
        $HTMLOUT .= stdmsg(_('Sorry'), _('All Peers Are Connectable!'));
    } else {
        $HTMLOUT .= '
        ' . _('This is only users that are active on the torrents right now.') . "<br>
        <p>
            <span class='has-text-danger'>*</span> " . _('means the user is seeding.') . "<br>
            $count " . _('unique users that are not connectable.') . '
        </p>';
        $heading = '
            <tr>
                <th>' . _('Username') . '</th>
                <th>' . _('Torrent') . '</th>
                <th>' . _('Client') . '</th>
            </tr>';
        $body = '';
        while ($arr2 = mysqli_fetch_assoc($res2)) {
            $body .= '
            <tr>
                <td>' . format_username((int) $arr2['userid']) . "</td>
                <td><a href='{$site_config['paths']['baseurl']}/details.php?id={$arr2['torrent']}&amp;dllist=1#seeders'>{$arr2['torrent']}</a>";
            if ($arr2['seeder'] === 'yes') {
                $body .= "<span class='has-text-danger'>*</span>";
            }
            $body .= '
                </td>
                <td>' . htmlsafechars($arr2['agent']) . '</td>
            </tr>';
        }
        $HTMLOUT .= main_table($body, $heading);
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dt = TIME_NOW;
    $msg = htmlsafechars($_POST['body']);
    if (!$msg) {
        stderr(_('Error'), 'Please Type In Some Text');
    }
    $fluent = $container->get(Database::class);
    $users = $fluent->from('peers')
                    ->select(null)
                    ->select('DISTINCT userid AS userid')
                    ->where('connectable = "no"');

    foreach ($users as $user) {
        $values[] = [
            'receiver' => $user['userid'],
            'added' => $dt,
            'msg' => $msg,
            'subject' => 'Connectability',
        ];
    }
    $session = $container->get(Session::class);
    if (!empty($values)) {
        $messages_class = $container->get(Message::class);
        $messages_class->insert($values);
        $values = [
            'user' => $CURUSER['id'],
            'date' => $dt,
        ];
        $fluent->insertInto('notconnectablepmlog')
               ->values($values)
               ->execute();
        $session->set('is-success', 'PM Sent to all non connectable peers');
    } else {
        $session->set('is-warning', 'No non connectable peers');
    }
}
if (isset($_GET['action1']) && htmlsafechars($_GET['action1']) === 'sendpm') {
    $HTMLOUT .= "
    <ul class='level-center bg-06'>
        <li class='is-link margin10'>
            <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=findnotconnectable&amp;action=findnotconnectable'>" . _('View the Log (Check this before PMing users)') . "</a>
        </li>
        <li class='is-link margin10'>
            <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=findnotconnectable&amp;action=findnotconnectable&amp;action1=list'>" . _('List Unconnectable Users') . "</a>
        </li>
    </ul>
    <div>
        <h1 class='has-text-centered'>" . _('Mass Message to All Non Connectable Users') . "</h1>
        <form method='post' action='{$_SERVER['PHP_SELF']}?tool=findnotconnectable&amp;action=findnotconnectable' enctype='multipart/form-data' accept-charset='utf-8'>";
    if (isset($_GET['returnto']) || isset($_SERVER['HTTP_REFERER'])) {
        $HTMLOUT .= "<input type='hidden' name='returnto' value='" . (isset($_GET['returnto']) ? htmlsafechars($_GET['returnto']) : htmlsafechars($_SERVER['HTTP_REFERER'])) . "'>";
    }
    $receiver = '';
    $body = _('The tracker has determined that you are firewalled or NATed and cannot accept incoming connections. 

This means that other peers in the swarm will be unable to connect to you, only you to them. Even worse, if two peers are both in this state they will not be able to connect at all. This has obviously a detrimental effect on the overall speed. 

The way to solve the problem involves opening the ports used for incoming connections (the same range you defined in your client) on the firewall and/or configuring your NAT server to use a basic form of NAT for that range instead of NAPT (the actual process differs widely between different router models. Check your router documentation and/or support forum. You will also find lots of information on the subject at PortForward). 

Also if you need help please come into our IRC chat room or post in the forums your problems. We are always glad to help out.

Thank You');
    $HTMLOUT .= main_div(BBcode($body, '', 250) . "
            <div class='has-text-centered'>
                <input type='submit' value='Send' class='button is-small'>
            </div>") . '
        </form>
    </div>';
}
if (isset($_GET['action1']) == '') {
    $getlog = sql_query('SELECT * FROM `notconnectablepmlog` ORDER BY date DESC LIMIT 20') or sqlerr(__FILE__, __LINE__);
    $HTMLOUT .= "
    <ul class='level-center bg-06'>
        <li class='is-link margin10'>
            <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=findnotconnectable&amp;action=findnotconnectable&amp;action1=sendpm'>" . _('Send All not connectable Users A PM') . "</a>
        </li>
        <li class='is-link margin10'>
            <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=findnotconnectable&amp;action=findnotconnectable&amp;action1=list'>" . _('List Unconnectable Users') . "</a>
        </li>
    </ul>
    <h1 class='has-text-centered'>" . _('Unconnectable Peers Mass PM Log') . '</h1>';
    if (mysqli_num_rows($getlog) > 0) {
        $HTMLOUT .= '
    <p>' . _('Please dont use the mass PM too often. we dont want to spam the users, just let them know they are unconnectable. Every week would be ok.') . '</p>';
        $heading = '
        <tr>
            <th>' . _('By User') . '</th>
            <th>' . _('Date') . '</th>
            <th>' . _('Elapsed') . '</th>
        </tr>';
        $body = '';
        while ($arr2 = mysqli_fetch_assoc($getlog)) {
            $elapsed = get_date((int) $arr2['date'], '', 0, 1);
            $body .= '
        <tr>
            <td>' . format_username((int) $arr2['user']) . '</td>
            <td>' . get_date((int) $arr2['date'], '') . "</td>
            <td>$elapsed</td>
        </tr>";
        }
        $HTMLOUT .= main_table($body, $heading);
    } else {
        $HTMLOUT .= stdmsg(_('Sorry'), _('All Peers Are Connectable!'));
    }
}
$title = _('Non Connectables');
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
