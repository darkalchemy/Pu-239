<?php

global $lang, $user;

if ('' != $user['browser']) {
    $browser = htmlsafechars($user['browser']);
} else {
    $browser = $lang['userdetails_nobrowser'];
}
$HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_user_browser']}</td><td>{$browser}</td></tr>";
