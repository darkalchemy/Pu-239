<?php

declare(strict_types = 1);

use Pu239\Database;

global $container, $CURUSER, $lang, $user;

$fluent = $container->get(Database::class);
if ($CURUSER['id'] != $user['id']) {
    if ($CURUSER['class'] >= UC_STAFF) {
        $showpmbutton = 1;
    } elseif ($user['acceptpms'] === 'yes') {
        $blocked = $fluent->from('blocks')
                          ->select('id')
                          ->where('userid = ?', $user['id'])
                          ->where('blockid = ?', $CURUSER['id'])
                          ->fetch();
        $showpmbutton = !empty($blocked) ? false : true;
    } elseif ($user['acceptpms'] === 'friends') {
        $friend = $fluent->from('friends')
                         ->select('id')
                         ->where('userid = ?', $user['id'])
                         ->where('friendid = ?', $CURUSER['id'])
                         ->fetch();
        $showpmbutton = !empty($friend) ? true : false;
    }
}
if (isset($showpmbutton)) {
    $HTMLOUT .= "
    <tr>
        <td colspan='2' class='has-text-centered'>
            <form method='get' action='messages.php?' enctype='multipart/form-data' accept-charset='utf-8'>
                <input type='hidden' name='action' value='send_message'>
                <input type='hidden' name='receiver' value='" . (int) $user['id'] . "'>
                <input type='hidden' name='returnto' value='" . urlencode($_SERVER['REQUEST_URI']) . "'>
                <input type='submit' value='{$lang['userdetails_msg_btn']}' class='button is-small'>
          </form>
        </td>
    </tr>";
}
