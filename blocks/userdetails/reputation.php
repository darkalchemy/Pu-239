<?php

$member_reputation = get_reputation($user, 'users');
$HTMLOUT .= "
    <tr>
        <td class='rowhead'>{$lang['userdetails_rep']}</td>
        <td>{$member_reputation}</td>
    </tr>";
