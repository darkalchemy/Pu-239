<?php

declare(strict_types = 1);
global $free, $user;

$HTMLOUT .= "
    <tr>
        <td class='rowhead'>" . _('Freeleech Slots:') . "</td><td>{$user['freeslots']}</td>
    </tr>
    <tr>
        <td class='rowhead'>" . _('Freeleech Status') . '</td>
        <td>' . ($user['free_switch'] != 0 ? _('FREE Status ') . ($user['free_switch'] > 1 ? _('Expires: ') . get_date((int) $user['free_switch'], 'DATE', 1, 0) . ' (' . mkprettytime($user['free_switch'] - TIME_NOW) . '' . _(' togo') . ') <br>' : '' . _('Unlimited') . '<br>') : _('None')) . '</td>
    </tr>';
