<?php

global $site_config, $lang, $user_stuffs, $CURUSER;

$user = $user_stuffs->getUserFromId($CURUSER['id']);
$HTMLOUT .= "
    <tr>
        <td class='rowhead'>{$lang['userdetails_bonus_points']}</td>
        <td>
            <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>" . number_format($user['seedbonus']) . '</a>
        </td>
    </tr>';
