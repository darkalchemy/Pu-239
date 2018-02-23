<?php

global $lang;

$HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_freeleech_slots']}</td><td>".(int) $user['freeslots'].'</td></tr>';
$HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_freeleech_status']}</td><td>".(0 != $user['free_switch'] ? $lang['userdetails_fstatus'].($user['free_switch'] > 1 ? $lang['userdetails_fexpire'].get_date($user['free_switch'], 'DATE').' ('.mkprettytime($user['free_switch'] - TIME_NOW).''.$lang['userdetails_ftogo'].') <br>' : ''.$lang['userdetails_funlimited'].'<br>') : $lang['userdetails_fnone']).'</td></tr>';
