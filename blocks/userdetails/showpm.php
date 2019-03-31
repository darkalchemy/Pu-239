<?php

global $CURUSER, $user_stuffs, $site_config, $lang, $user;

if ($CURUSER['id'] != $user['id']) {
    if ($CURUSER['class'] >= UC_STAFF) {
        $showpmbutton = 1;
    } elseif ($user['acceptpms'] === 'yes') {
        $r = sql_query('SELECT id FROM blocks WHERE userid = ' . sqlesc($user['id']) . ' AND blockid = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
        $showpmbutton = (mysqli_num_rows($r) == 1 ? 0 : 1);
    } elseif ($user['acceptpms'] === 'friends') {
        $r = sql_query('SELECT id FROM friends WHERE userid = ' . sqlesc($user['id']) . ' AND friendid = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
        $showpmbutton = (mysqli_num_rows($r) == 1 ? 1 : 0);
    }
}
if (isset($showpmbutton)) {
    $HTMLOUT .= "
    <tr>
        <td colspan='2' class='has-text-centered'>
            <form method='get' action='messages.php?' accept-charset='utf-8'>
                <input type='hidden' name='action' value='send_message'>
                <input type='hidden' name='receiver' value='" . (int) $user['id'] . "'>
                <input type='hidden' name='returnto' value='" . urlencode($_SERVER['REQUEST_URI']) . "'>
                <input type='submit' value='{$lang['userdetails_msg_btn']}' class='button is-small'>
          </form>
        </td>
    </tr>";
}
