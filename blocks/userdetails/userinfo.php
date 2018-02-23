<?php

if ($user['info']) {
    $HTMLOUT .= "<tr><td colspan='2' class='text' bgcolor='#F4F4F0'>".format_comment($user['info'])."</td></tr>\n";
}
if ($user['signature']) {
    $HTMLOUT .= "<tr><td colspan='2' class='text' bgcolor='#F4F4F0'>".format_comment($user['signature'])."</td></tr>\n";
}
