<?php

declare(strict_types = 1);
global $lang, $onlinetime;

if ($user['onlinetime'] > 0) {
    $onlinetime = time_return($user['onlinetime']);
    $HTMLOUT .= "
    <tr>
        <td class='rowhead'>{$lang['userdetails_time_online']}</td>
        <td>{$onlinetime}</td>
    </tr>";
} else {
    $onlinetime = $lang['userdetails_notime_online'];
    $HTMLOUT .= "
    <tr>
        <td class='rowhead'>{$lang['userdetails_time_online']}</td>
        <td>{$onlinetime}</td>
    </tr>";
}
