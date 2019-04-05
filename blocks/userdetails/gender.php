<?php

$HTMLOUT .= "
    <tr>
        <td class='rowhead'>{$lang['userdetails_gender']}</td>
        <td>
            <img src='{$site_config['paths']['images_baseurl']}" . htmlsafechars($user['gender']) . ".gif' alt='" . htmlsafechars($user['gender']) . "' title='" . htmlsafechars($user['gender']) . "'>
        </td>
    </tr>";
