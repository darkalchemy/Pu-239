<?php

declare(strict_types = 1);
global $onlinetime;

if ($user['onlinetime'] > 0) {
    $onlinetime = time_return($user['onlinetime']);
    $HTMLOUT .= "
    <tr>
        <td class='rowhead'>" . _('Total Online') . "</td>
        <td>{$onlinetime}</td>
    </tr>";
} else {
    $onlinetime = _('This user has no online time recorded');
    $HTMLOUT .= "
    <tr>
        <td class='rowhead'>" . _('Total Online') . "</td>
        <td>{$onlinetime}</td>
    </tr>";
}
