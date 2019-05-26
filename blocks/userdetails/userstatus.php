<?php

declare(strict_types = 1);
global $CURUSER, $user_status;

if ($user['paranoia'] < 1 || $CURUSER['id'] == $id || $CURUSER['class'] >= UC_STAFF) {
    if (isset($user_status['last_status'])) {
        $HTMLOUT .= "
    <tr>
        <td class='rowhead'>{$lang['userdetails_status']}</td>
        <td>" . format_urls($user_status['last_status']) . '<br><small>added ' . get_date((int) $user_status['last_update'], '', 0, 1) . '</small></td>
    </tr>';
    }
}
