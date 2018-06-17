<?php

global $lang;

if ($user['avatar']) {
    $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_avatar']}</td><td><img src='" . url_proxy($user['avatar'], true, $user['av_w'], $user['av_h']) . "' width='{$user['av_w']}' height='{$user['av_h']}' alt='' /></td></tr>\n";
}
