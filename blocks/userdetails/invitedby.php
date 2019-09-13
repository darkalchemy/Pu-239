<?php

declare(strict_types = 1);

use Pu239\Database;

global $container, $site_config, $viewer;

$type = !empty($user['join_type']) ? $user['join_type'] : 'open';
$invite_by = $user['invitedby'];
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
$invited = $fluent->from('users AS u')
                  ->select(null)
                  ->select('u.id')
                  ->select('u.email')
                  ->select('u.uploaded')
                  ->select('u.downloaded')
                  ->select('i.status')
                  ->leftJoin('invite_codes AS i ON u.id = i.receiver')
                  ->where('u.invitedby = ?', $viewer['id'])
                  ->where('u.join_type = "invite"')
                  ->orderBy('u.registered')
                  ->fetchAll();

$inviteted_by_this_member = '';
if (empty($invited)) {
    $inviteted_by_this_member .= 'No invitees yet.';
} else {
    $heading = '
        <tr>
            <th><b>' . $lang['userdetails_username'] . '</b></th>
            <th><b>' . $lang['userdetails_email'] . '</b></th>
            <th><b>' . $lang['userdetails_uploaded'] . '</b></th>' . ($site_config['site']['ratio_free'] ? '' : '
            <th><b>' . $lang['userdetails_downloaded'] . '</b></th>') . '
            <th><b>' . $lang['userdetails_ratio'] . '</b></th>
            <th><b>' . $lang['userdetails_status'] . '</b></th>
       </tr>';
    $body = '';
    foreach ($invited as $arr_invited) {
        $body .= '
        <tr>
            <td>' . ($arr_invited['status'] === 'Pending' ? format_comment($arr_invited['username']) : format_username($arr_invited['id']) . '<br>') . '</td>
            <td>' . format_comment($arr_invited['email']) . '</td>
            <td>' . mksize($arr_invited['uploaded']) . '</td>' . ($site_config['site']['ratio_free'] ? '' : '
            <td>' . mksize($arr_invited['downloaded']) . '</td>') . '
            <td>' . member_ratio((float) $arr_invited['uploaded'], (float) $arr_invited['downloaded']) . '</td>
            <td>' . ($arr_invited['status'] === 'Confirmed' ? '
                <span class="has-text-success">' . $lang['userdetails_confirmed'] . '</span>
            </td>
        </tr>' : '
                <span class="has-text-danger">' . $lang['userdetails_pending'] . '</span>
            </td>
        </tr>');
    }
    $inviteted_by_this_member = main_table($body, $heading);
}

$the_flip_box_5 = '[ <a id="invites"></a><a class="is-link" href="#invites" onclick="flipBox(\'5\')" id="b_5" title="' . $lang['userdetails_open_close_inv'] . '">' . $lang['userdetails_inv_view'] . '<img onclick="flipBox(\'5\')" src="' . $site_config['paths']['images_baseurl'] . 'panel_on.gif" id="b_5" style="vertical-align:middle;"  width="8" height="8" alt="' . $lang['userdetails_open_close_inv1'] . '" title="' . $lang['userdetails_open_close_inv1'] . '" /></a> ] [ <a class="is-link" href="' . $site_config['paths']['baseurl'] . '/staffpanel.php?tool=invite_tree&amp;action=invite_tree&amp;id=' . $viewer['id'] . '" title="' . $lang['userdetails_inv_click'] . '">' . $lang['userdetails_inv_viewt'] . '</a> ]';
$HTMLOUT .= '<tr><td class="rowhead">' . $lang['userdetails_invitees'] . '</td><td>' . (!empty($invited) ? $the_flip_box_5 . '<div id="box_5" style="display: none">
    <br>' . $inviteted_by_this_member . '</div>' : $lang['userdetails_no_invitees']) . '</td></tr>';
