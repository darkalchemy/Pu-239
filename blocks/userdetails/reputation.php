<?php

declare(strict_types = 1);
global $user;

$member_reputation = get_reputation($user, 'users');
$HTMLOUT .= "
    <tr>
        <td class='rowhead'>" . _('Reputation') . "</td>
        <td>{$member_reputation}</td>
    </tr>";
