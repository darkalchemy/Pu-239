<?php

declare(strict_types = 1);
global $free, $user;

$HTMLOUT .= "
    <tr>
        <td class='rowhead'>{$lang['userdetails_freeleech_slots']}</td><td>{$user['freeslots']}</td>
    </tr>
    <tr>
        <td class='rowhead'>{$lang['userdetails_freeleech_status']}</td>
        <td>" . ($user['free_switch'] != 0 ? $lang['userdetails_fstatus'] . ($user['free_switch'] > 1 ? $lang['userdetails_fexpire'] . get_date((int) $user['free_switch'], 'DATE') . ' (' . mkprettytime($user['free_switch'] - TIME_NOW) . '' . $lang['userdetails_ftogo'] . ') <br>' : '' . $lang['userdetails_funlimited'] . '<br>') : $lang['userdetails_fnone']) . '</td>
    </tr>';
