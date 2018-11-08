<?php

global $CURUSER, $site_config, $lang;

$count_snatched = $count2 = $dlc = '';
if ($CURUSER['class'] >= UC_STAFF) {
    $table_data .= "
        <tr>
            <td class='rowhead'>{$lang['userdetails_snatched']}</td>
            <td>
                <a id='snatched-staff-hash'></a>
                <fieldset id='snatched-staff' class='header'>
                    <legend class='flipper size_4'><i class='icon-down-open' aria-hidden='true'></i><span class='has-text-red'>*Staff Only*</span> View Snatched Torrents</legend>
                    <div id='inner_snatched_staff' data-uid='{$user['id']}' data-csrf='" . $session->get('csrf_token') . "'></div>
                </fieldset>
            </td>
        </tr>";
}

