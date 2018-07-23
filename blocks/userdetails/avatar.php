<?php

global $lang;

if ($user['avatar']) {
    $HTMLOUT .= "
    <tr>
        <td class='rowhead'>{$lang['userdetails_avatar']}</td>
        <td><img src='" . url_proxy($user['avatar'], true, 150) . "' alt='Avatar' /></td>
    </tr>\n";
}
