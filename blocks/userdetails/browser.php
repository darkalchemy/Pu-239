<?php

declare(strict_types = 1);

if ($user['browser'] != '') {
    $browser = htmlsafechars($user['browser']);
} else {
    $browser = _('No browser recorded yet');
}
$HTMLOUT .= "<tr><td class='rowhead'>" . _('User Browser') . "</td><td>{$browser}</td></tr>";
