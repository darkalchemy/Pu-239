<?php

global $lang;

if ($user['avatar']) {
    $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_avatar']}</td><td><img src='".image_proxy($user['avatar'])."' width='{$user['av_w']}' height='{$user['av_h']}' alt='' /></td></tr>\n";
}
