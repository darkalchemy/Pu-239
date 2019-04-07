<?php

global $CURUSER, $site_config, $lang, $user;

if ($user['invitedby'] > 0) {
    $res_get_invitor = sql_query('SELECT id, class, username, warned, suspended, enabled, donor, chatpost, leechwarn, pirate, king FROM users WHERE id=' . sqlesc($user['invitedby'])) or sqlerr(__FILE__, __LINE__);
    $user_get_invitor = mysqli_fetch_assoc($res_get_invitor);
    $HTMLOUT .= '<tr><td class="rowhead">' . $lang['userdetails_invited_by'] . '</td><td>' . format_username($user_get_invitor['id']) . '</td></tr>';
} else {
    $HTMLOUT .= '<tr><td class="rowhead">' . $lang['userdetails_invited_by'] . '</td><td><b>' . $lang['userdetails_iopen_s'] . '</b></td></tr>';
}
$rez_invited = sql_query('SELECT id, class, username, email, uploaded, downloaded, status, warned, suspended, enabled, donor, email, INET6_NTOA(ip) AS ip, chatpost, leechwarn, pirate, king FROM users WHERE invitedby = ' . sqlesc($user['id']) . ' ORDER BY added') or sqlerr(__FILE__, __LINE__);
$inviteted_by_this_member = '';
if (mysqli_num_rows($rez_invited) < 1) {
    $inviteted_by_this_member .= 'No invitees yet.';
} else {
    $inviteted_by_this_member .= '<table width="100%" border="1">
        <tr><td class="colhead"><b>' . $lang['userdetails_u_ip'] . '</b></td>
        <td class="colhead"><b>' . $lang['userdetails_email'] . 'l</b></td>
        <td class="colhead"><b>' . $lang['userdetails_uploaded'] . '</b></td>
        ' . ($site_config['site']['ratio_free'] ? '' : '<td class="colhead"><b>' . $lang['userdetails_downloaded'] . '</b></td>') . '
        <td class="colhead"><b>' . $lang['userdetails_ratio'] . '</b></td>
        <td class="colhead"><b>' . $lang['userdetails_status'] . '</b></td></tr>';
    while ($arr_invited = mysqli_fetch_assoc($rez_invited)) {
        $inviteted_by_this_member .= '<tr><td>' . ($arr_invited['status'] === 'pending' ? htmlsafechars($arr_invited['username']) : format_username($arr_invited['id']) . '<br> ' . ($CURUSER['class'] < UC_STAFF ? '' : $arr_invited['ip'])) . '</td>
        <td>' . htmlsafechars($arr_invited['email']) . '</td>
        <td>' . mksize($arr_invited['uploaded']) . '</td>
        ' . ($site_config['site']['ratio_free'] ? '' : '<td>' . mksize($arr_invited['downloaded']) . '</td>') . '
        <td>' . member_ratio($arr_invited['uploaded'], $site_config['site']['ratio_free'] ? '0' : $arr_invited['downloaded']) . '</td>
        <td>' . ($arr_invited['status'] === 'confirmed' ? '<span style="color: green;">' . $lang['userdetails_confirmed'] . '</span></td></tr>' : '<span style="color: red;">' . $lang['userdetails_pending'] . '</span></td></tr>');
    }
    $inviteted_by_this_member .= '</table>';
}
$the_flip_box_5 = ')" name="b_5" title="' . $lang['userdetails_open_close_inv'] . '">' . $lang['userdetails_inv_view'] . ')" src="' . $site_config['paths']['images_baseurl'] . 'panel_on.gif" name="b_5" style="vertical-align:middle;"  width="8" height="8" alt="' . $lang['userdetails_open_close_inv1'] . '" title="' . $lang['userdetails_open_close_inv1'] . '"></a> ] [ <a class="altlink" href="' . $site_config['paths']['baseurl'] . '/staffpanel.php?tool=invite_tree&amp;action=invite_tree&amp;id=' . (int) $user['id'] . '" title="' . $lang['userdetails_inv_click'] . '">' . $lang['userdetails_inv_viewt'] . '</a> ]';
$HTMLOUT .= '<tr><td class="rowhead">' . $lang['userdetails_invitees'] . '</td><td>' . (mysqli_num_rows($rez_invited) > 0 ? $the_flip_box_5 . '<div id="box_5" style="display:none">
    <br>' . $inviteted_by_this_member . '</div>' : $lang['userdetails_no_invitees']) . '</td></tr>';
