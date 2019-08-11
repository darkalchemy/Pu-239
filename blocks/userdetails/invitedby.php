<?php

declare(strict_types = 1);

use Pu239\Database;

global $container, $site_config, $CURUSER;

$type = !empty($user['join_type']) ? $user['join_type'] : 'open';
$invite_by = (int) $user['invitedby'];

if ($invite_by > 0 && $type === 'invite') {
    $HTMLOUT .= '
        <tr>
            <td class="rowhead">' . $lang['userdetails_invited_by'] . '</td>
            <td>' . format_username($invite_by) . '</td>
        </tr>';
} elseif ($invite_by > 0 && $type === 'promo') {
    $fluent = $container->get(Database::class);
    $name = $fluent->from('promo')
                   ->select(null)
                   ->select('name')
                   ->where('id = ?', $invite_by)
                   ->fetch('name');
    $name = !empty($name) ? htmlsafechars($name) : 'Promo has been Deleted';
    $HTMLOUT .= '
        <tr>
            <td class="rowhead">' . $lang['userdetails_invited_by'] . '</td>
            <td><a href="' . $site_config['paths']['baseurl'] . '/promo.php">Promo: ' . $name . '</a></td>
        </tr>';
} else {
    $HTMLOUT .= '
        <tr>
            <td class="rowhead">' . $lang['userdetails_invited_by'] . '</td>
            <td><b>' . $lang['userdetails_iopen_s'] . '</b></td>
        </tr>';
}
$users = $fluent->from('users AS u')
    ->select(null)
    ->select('u.id')
    ->select('i.status')
    ->leftJoin('invite_codes AS i ON u.id = i.receiver')
    ->where('u.invitedby = ?', $user['id'])
    ->where('u.join_type = "invite"')
    ->orderBy('u.registered')
    ->fetchAll();

$rez_invited = sql_query('SELECT id, class, username, email, uploaded, downloaded, warned, status, donor, email, chatpost, leechwarn, pirate, king FROM users WHERE invitedby = ' . sqlesc($user['id']) . ' ORDER BY registered') or sqlerr(__FILE__, __LINE__);
$inviteted_by_this_member = '';
if (mysqli_num_rows($rez_invited) < 1) {
    $inviteted_by_this_member .= 'No invitees yet.';
} else {
    $inviteted_by_this_member .= '<table>
        <td class="colhead"><b>' . $lang['userdetails_email'] . 'l</b></td>
        <td class="colhead"><b>' . $lang['userdetails_uploaded'] . '</b></td>
        ' . ($site_config['site']['ratio_free'] ? '' : '<td class="colhead"><b>' . $lang['userdetails_downloaded'] . '</b></td>') . '
        <td class="colhead"><b>' . $lang['userdetails_ratio'] . '</b></td>
        <td class="colhead"><b>' . $lang['userdetails_status'] . '</b></td></tr>';
    while ($arr_invited = mysqli_fetch_assoc($rez_invited)) {
        $inviteted_by_this_member .= '<tr><td>' . ($arr_invited['status'] === 'Pending' ? htmlsafechars($arr_invited['username']) : format_username((int) $arr_invited['id']) . '<br>') . '</td>
        <td>' . htmlsafechars($arr_invited['email']) . '</td>
        <td>' . mksize($arr_invited['uploaded']) . '</td>
        ' . ($site_config['site']['ratio_free'] ? '' : '<td>' . mksize($arr_invited['downloaded']) . '</td>') . '
        <td>' . member_ratio((int) $arr_invited['uploaded'], (int) $arr_invited['downloaded']) . '</td>
        <td>' . ($arr_invited['status'] === 'Confirmed' ? '<span class="has-text-success">' . $lang['userdetails_confirmed'] . '</span></td></tr>' : '<span class="has-text-danger">' . $lang['userdetails_pending'] . '</span></td></tr>');
    }
    $inviteted_by_this_member .= '</table>';
}

$the_flip_box_5 = '[ <a id="invites"></a><a class="is-link" href="#invites" onclick="flipBox(\'5\')" id="b_5" title="' . $lang['userdetails_open_close_inv'] . '">' . $lang['userdetails_inv_view'] . '<img onclick="flipBox(\'5\')" src="' . $site_config['paths']['images_baseurl'] . 'panel_on.gif" id="b_5" style="vertical-align:middle;"  width="8" height="8" alt="' . $lang['userdetails_open_close_inv1'] . '" title="' . $lang['userdetails_open_close_inv1'] . '" /></a> ] [ <a class="is-link" href="' . $site_config['paths']['baseurl'] . '/staffpanel.php?tool=invite_tree&amp;action=invite_tree&amp;id=' . (int) $user['id'] . '" title="' . $lang['userdetails_inv_click'] . '">' . $lang['userdetails_inv_viewt'] . '</a> ]';
$HTMLOUT .= '<tr><td class="rowhead">' . $lang['userdetails_invitees'] . '</td><td>' . (mysqli_num_rows($rez_invited) > 0 ? $the_flip_box_5 . '<div id="box_5" style="display: none">
    <br>' . $inviteted_by_this_member . '</div>' : $lang['userdetails_no_invitees']) . '</td></tr>';
