<?php

declare(strict_types = 1);
global $free, $user;

$personal_freeleech = strtotime($user['personal_freeleech']);
$HTMLOUT .= "
    <tr>
        <td class='rowhead'>" . _('Freeleech Slots') . ":</td><td>{$user['freeslots']}</td>
    </tr>
    <tr>
        <td class='rowhead'>" . _('Freeleech Status') . '</td>
        <td>' . ($personal_freeleech != 0 ? _('FREE Status ') . ($personal_freeleech > 1 ? _('Expires: ') . get_date($personal_freeleech, 'DATE', 1, 0) . ' (' . mkprettytime($personal_freeleech - TIME_NOW) . '' . _(' togo') . ') <br>' : '' . _('Unlimited') . '<br>') : _('None')) . '</td>
    </tr>';
