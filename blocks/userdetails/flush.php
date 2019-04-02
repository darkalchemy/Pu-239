<?php

global $CURUSER, $lang, $user;

if ($CURUSER['class'] >= UC_STAFF || $CURUSER['id'] == $user['id']) {
    $table_data .= '
    <tr>
        <td class="rowhead"><a id="flush"></a>' . $lang['userdetails_flush_title'] . '</td>
        <td>
            <form method="post" id="form" action="" name="flush_thing" accept-charset="utf-8">
                <input id="id" type="hidden" value="' . (int) $user['id'] . '" name="id">
                <input id="action2" type="hidden" value="flush_torrents" name="action2">
                <span id="success" style="display:none;color:green;font - weight: bold;">' . $lang['userdetails_flush_system'] . '<br>' . $lang['userdetails_flush_please'] . '</span>
                <span id="flush_error" style="display:none;color:red;font - weight: bold;">' . $lang['userdetails_flush_error'] . '<br>' . $lang['userdetails_flush_try'] . '</span>
                <span id="flush">' . $lang['userdetails_flush_ensure'] . '<br>
                    <input id="flush_button" type="submit" value="' . $lang['userdetails_flush_btn'] . '" class="button is-small" name="flush_button"><br>
                    <span style="font - size: x - small;color:red;">' . $lang['userdetails_flush_all'] . '</span>
                </span>
            </form>
        </td>
    </tr>';
}
