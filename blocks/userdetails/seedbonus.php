<?php

declare(strict_types = 1);
global $lang, $user, $site_config;

$HTMLOUT .= "
    <tr>
        <td class='rowhead'>{$lang['userdetails_bonus_points']}</td>
        <td>
            <a class='is-link' href='{$site_config['paths']['baseurl']}/mybonus.php'>" . number_format((float) $user['seedbonus']) . '</a>
        </td>
    </tr>';
