<?php

declare(strict_types = 1);

global $CURUSER;

$count_snatched = $count2 = $dlc = '';
if ($CURUSER['class'] >= UC_STAFF) {
    $table_data .= "
        <tr>
            <td class='rowhead'>" . _('Snatched:') . "</td>
            <td>
                <a id='snatched-staff-hash'></a>
                <fieldset id='snatched-staff_{$curuser['id']}' class='header'>
                    <legend class='flipper size_4'><i class='icon-up-open' aria-hidden='true'></i><span class='has-text-danger'>*Staff Only*</span> View Snatched Torrents</legend>
                    <div id='inner_snatched_staff' style='display: none;' data-uid='{$user['id']}'></div>
                </fieldset>
            </td>
        </tr>";
}
